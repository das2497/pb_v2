<?php

namespace App\Http\Controllers;

use App\Exceptions\AIServiceUnavailableException;
use App\Services\AIQueryService;
use Illuminate\Http\Request;

class AIChatController extends Controller
{
    public function __construct(private AIQueryService $queries)
    {
    }

    public function index()
    {
        return view('chat');
    }

    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'model' => 'required|string',
        ]);

        $userQuestion = trim($validated['message']);
        $model = $validated['model'];
        $isPrediction = $this->queries->isPredictionQuery($userQuestion);
        $limit = $isPrediction ? AIQueryService::PREDICTION_LIMIT : AIQueryService::DEFAULT_LIMIT;

        try {
            $generatedQuery = $this->queries->generateQuery($userQuestion, $model, $isPrediction, $limit);
        } catch (AIServiceUnavailableException $e) {
            return response()->json(['reply' => $e->getMessage()], 502);
        }

        if ($generatedQuery === 'UNSUPPORTED_QUERY') {
            return response()->json(['reply' => 'Sorry, your question cannot be answered using the available data.']);
        }

        if ($rejectionReason = $this->queries->isSafeSelectQuery($generatedQuery)) {
            return response()->json(['reply' => 'Sorry, that question cannot be answered safely: ' . $rejectionReason], 400);
        }

        try {
            $queryResults = $this->queries->executeQuery($generatedQuery);
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Failed to execute query: ' . $e->getMessage()], 400);
        }

        if (empty($queryResults)) {
            return response()->json(['reply' => 'No data found to answer your question.']);
        }

        $payload = $this->queries->compressIfNeeded($queryResults, $isPrediction);

        try {
            $finalAnswer = $this->queries->generateAnswer($userQuestion, $payload, $isPrediction, $model);
        } catch (AIServiceUnavailableException $e) {
            return response()->json(['reply' => $e->getMessage()], 502);
        }

        return response()->json(['reply' => $finalAnswer]);
    }
}
