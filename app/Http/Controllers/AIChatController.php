<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AIChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    private const DB_SCHEMA = <<<SQL
CREATE TABLE password_reset_tokens (email VARCHAR(255), token VARCHAR(255), created_at TIMESTAMP NULL);
CREATE TABLE products (id BIGINT PRIMARY KEY AUTO_INCREMENT, item_number VARCHAR(255), name_english VARCHAR(255), name_sinhala VARCHAR(255), visibility VARCHAR(255), category VARCHAR(255), unit_price VARCHAR(255), mrp VARCHAR(255), direct_sale_price VARCHAR(255), img VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE users (id BIGINT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), email VARCHAR(255), role VARCHAR(255), email_verified_at TIMESTAMP NULL, password VARCHAR(255), remember_token VARCHAR(100), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE product_categories (id BIGINT PRIMARY KEY AUTO_INCREMENT, category VARCHAR(255), main_category VARCHAR(50), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE shops (id BIGINT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), sinhala_name VARCHAR(50), branch_code VARCHAR(255), email VARCHAR(255), contact VARCHAR(255), price_range VARCHAR(255), order_time VARCHAR(255), morning_route VARCHAR(255), evening_route VARCHAR(255), type VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE reps (id BIGINT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), email VARCHAR(255), contact VARCHAR(255), type VARCHAR(255), access VARCHAR(20), password VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE routes (id BIGINT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), `index` VARCHAR(255), type VARCHAR(255), time VARCHAR(50), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE orders (id BIGINT PRIMARY KEY AUTO_INCREMENT, unique_id VARCHAR(255), shop VARCHAR(255), total_price DOUBLE, note TEXT, time_period VARCHAR(255), status VARCHAR(255), default_name VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE carts (id BIGINT PRIMARY KEY AUTO_INCREMENT, item_number VARCHAR(255), qty VARCHAR(255), price VARCHAR(255), remark VARCHAR(255), shop_c_number VARCHAR(255), order_number VARCHAR(255), rep VARCHAR(255), default_name VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE rep_assign_shops (id BIGINT PRIMARY KEY AUTO_INCREMENT, rep_id VARCHAR(255), shop_id VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
CREATE TABLE logs (id BIGINT PRIMARY KEY AUTO_INCREMENT, type VARCHAR(255), message TEXT, user VARCHAR(255), created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL);
SQL;

private const PREDICTION_KEYWORDS = [
    'predict', 'forecast', 'trend', 'future', 'next', 'estimate',
    'expect', 'projection', 'growth', 'decline', 'pattern', 'analysis',
    'historical', 'over time', 'monthly', 'weekly', 'yearly', 'annually',
];

private const PREDICTION_LIMIT = 1000;
private const DEFAULT_LIMIT     = 100;
private const MAX_JSON_CHARS    = 60000; // safe token budget (~15k tokens)

private function isPredictionQuery(string $question): bool
{
    $lower = strtolower($question);
    foreach (self::PREDICTION_KEYWORDS as $keyword) {
        if (str_contains($lower, $keyword)) {
            return true;
        }
    }
    return false;
}

/**
 * If the JSON payload is too large, aggregate it by a detected date column
 * so the second LLM call stays within the token budget.
 */
private function compressIfNeeded(array $rows, bool $isPrediction): array
{
    $json = json_encode($rows, JSON_PRETTY_PRINT);

    if (strlen($json) <= self::MAX_JSON_CHARS || !$isPrediction) {
        return [
            'data'        => $rows,
            'compressed'  => false,
            'total_rows'  => count($rows),
        ];
    }

    // Detect a date/time column to group by (month granularity)
    $dateColumn = null;
    if (!empty($rows[0])) {
        foreach (array_keys((array) $rows[0]) as $col) {
            if (preg_match('/date|time|created|updated|period/i', $col)) {
                $dateColumn = $col;
                break;
            }
        }
    }

    // If no date column found, slice to what fits
    if (!$dateColumn) {
        $sliced = [];
        $budget = 0;
        foreach ($rows as $row) {
            $chunk   = json_encode($row);
            $budget += strlen($chunk);
            if ($budget > self::MAX_JSON_CHARS) break;
            $sliced[] = $row;
        }
        return [
            'data'        => $sliced,
            'compressed'  => true,
            'total_rows'  => count($rows),
            'note'        => 'Dataset truncated to fit context window. Full row count: ' . count($rows),
        ];
    }

    // Aggregate numeric columns by month
    $grouped = [];
    foreach ($rows as $row) {
        $row    = (array) $row;
        $period = substr($row[$dateColumn] ?? 'unknown', 0, 7); // YYYY-MM
        if (!isset($grouped[$period])) {
            $grouped[$period] = ['period' => $period, '_count' => 0];
        }
        $grouped[$period]['_count']++;
        foreach ($row as $col => $val) {
            if ($col === $dateColumn) continue;
            if (is_numeric($val)) {
                $grouped[$period][$col . '_sum'] = ($grouped[$period][$col . '_sum'] ?? 0) + $val;
            }
        }
    }

    ksort($grouped);

    return [
        'data'        => array_values($grouped),
        'compressed'  => true,
        'total_rows'  => count($rows),
        'aggregation' => 'Monthly aggregation applied due to large dataset size',
    ];
}

public function chat(Request $request)
{
    $validated = $request->validate([
        'message' => 'required|string|max:500',
        'model'   => 'required|string',
    ]);

    $userQuestion  = trim($validated['message']);
    $model         = $validated['model'];
    $isPrediction  = $this->isPredictionQuery($userQuestion);
    $limit         = $isPrediction ? self::PREDICTION_LIMIT : self::DEFAULT_LIMIT;

    // ── Step 1: Generate SQL query ────────────────────────────────────────────
    $queryGeneratorPrompt = "You are a precise MySQL query generator.

Output rules:
- Return ONLY a raw SQL SELECT query
- No explanations, no markdown, no backticks, no commentary

Query rules:
- Only SELECT statements — never INSERT, UPDATE, DELETE, DROP, TRUNCATE, or ALTER
- Never include the \`password\` column in any query, even if explicitly requested
- Always append LIMIT {$limit} unless the user specifies a different count
- If the question cannot be answered using the schema below, return exactly: UNSUPPORTED_QUERY
- On the orders table, the shop column contains branch_codes, not shop names
" . ($isPrediction ? "- This is a prediction/trend question: include date/time columns and order by date ASC to expose chronological patterns\n" : "") . "
Schema:
" . self::DB_SCHEMA . "

User question: " . $userQuestion;

    $queryResponse = Http::timeout(30)
        ->withHeaders(['Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY')])
        ->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'    => $model,
            'messages' => [['role' => 'user', 'content' => $queryGeneratorPrompt]],
        ]);

    if ($queryResponse->failed()) {
        return response()->json(['reply' => 'AI service unavailable. Please try again with another model.'], 502);
    }

    $generatedQuery = trim($queryResponse['choices'][0]['message']['content'] ?? 'UNSUPPORTED_QUERY');

    if ($generatedQuery === 'UNSUPPORTED_QUERY') {
        return response()->json(['reply' => 'Sorry, your question cannot be answered using the available data.']);
    }

    // ── Step 2: Execute the generated SQL query ───────────────────────────────
    try {
        $queryResults = DB::select($generatedQuery);
    } catch (\Exception $e) {
        return response()->json(['reply' => 'Failed to execute query: ' . $e->getMessage()], 400);
    }

    if (empty($queryResults)) {
        return response()->json(['reply' => 'No data found to answer your question.']);
    }

    // ── Step 3: Compress payload if needed ────────────────────────────────────
    $payload = $this->compressIfNeeded($queryResults, $isPrediction);

    // ── Step 4: Interpret results and answer ──────────────────────────────────
    $compressionNote = $payload['compressed']
        ? "\nNote: " . ($payload['aggregation'] ?? $payload['note'] ?? 'Data was compressed.') . " Total rows in DB: " . $payload['total_rows'] . ".\n"
        : '';

    $answerGeneratorPrompt = "You are a helpful business data analyst assistant.
A user asked a question and we fetched relevant data from our database.
" . ($isPrediction ? "This is a prediction or trend analysis request. Identify patterns, seasonality, and growth/decline trends in the data. Provide a forward-looking insight or forecast based on the historical data.\n" : "") . "
User question: " . $userQuestion . "
{$compressionNote}
Database results (JSON):
" . json_encode($payload['data'], JSON_PRETTY_PRINT) . "

Instructions:
- Answer the user's question directly and clearly based only on the data provided
- Be concise but informative — use natural language, not raw data dumps
- If the data contains numbers or counts, summarize them meaningfully
" . ($isPrediction ? "- Highlight trends, peaks, and patterns clearly\n- Provide a prediction or forecast with reasoning\n- Mention any data limitations if the dataset was aggregated or truncated\n" : "") . "- Do not mention SQL, databases, or technical details in your response";

    $answerResponse = Http::timeout(60)
        ->withHeaders(['Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY')])
        ->post('https://openrouter.ai/api/v1/chat/completions', [
            'model'    => $model,
            'messages' => [['role' => 'user', 'content' => $answerGeneratorPrompt]],
        ]);

    if ($answerResponse->failed()) {
        return response()->json(['reply' => 'Could not interpret the results. Please try again.'], 502);
    }

    $finalAnswer = trim($answerResponse['choices'][0]['message']['content'] ?? 'Could not generate an answer.');

    return response()->json(['reply' => $finalAnswer]);
}

}