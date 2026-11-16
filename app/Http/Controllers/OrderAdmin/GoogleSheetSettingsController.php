<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Setting;
use App\Services\GoogleSheetExportService;
use App\Services\GoogleSheetSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Order Admin > Google Sheets: sets up the export that the dashboard's
 * "Refresh ..." buttons use, without touching .env or redeploying.
 *
 * Three things are configurable here:
 *  - the service-account JSON key file (uploaded, stored outside the
 *    public directory);
 *  - one spreadsheet ID per report type;
 *  - a per-report "Test" that actually opens the sheet with those
 *    credentials, so a wrong ID or an unshared sheet is caught here
 *    rather than halfway through a refresh.
 *
 * A field left blank falls back to config/services.php (the .env vars),
 * so an installation that already configured these by env keeps working
 * exactly as before.
 */
class GoogleSheetSettingsController extends Controller
{
    /** Where an uploaded service-account key is kept - private storage, never public/. */
    private function credentialsStoragePath(): string
    {
        return storage_path('app/google/credentials.json');
    }

    public function index()
    {
        $reports = [];

        foreach (GoogleSheetSettings::REPORT_LABELS as $type => $label) {
            $reports[] = [
                'type' => $type,
                'label' => $label,
                'saved_id' => GoogleSheetSettings::savedSpreadsheetId($type),
                'effective_id' => GoogleSheetSettings::spreadsheetId($type),
                'env_id' => config('services.google_sheets.spreadsheets.' . $type),
            ];
        }

        return view('order-admin.google-sheet-settings', [
            'reports' => $reports,
            'credentialsPath' => GoogleSheetSettings::credentialsPath(),
            'credentialsExist' => GoogleSheetSettings::credentialsExist(),
            'credentialsInfo' => GoogleSheetSettings::credentialsInfo(),
        ]);
    }

    /**
     * Saves the spreadsheet IDs. Accepts either a bare ID or a full
     * Google Sheets URL pasted from the browser - the ID is extracted
     * from the URL, since that is what an admin actually has at hand.
     */
    public function update(Request $request)
    {
        $types = GoogleSheetSettings::reportTypes();

        $rules = [];
        $names = [];

        foreach ($types as $type) {
            $rules["spreadsheets.$type"] = ['nullable', 'string', 'max:255'];
            $names["spreadsheets.$type"] = GoogleSheetSettings::REPORT_LABELS[$type] . ' spreadsheet';
        }

        $validated = $request->validate($rules, [], $names);

        $changed = [];

        foreach ($types as $type) {
            $raw = $validated['spreadsheets'][$type] ?? null;
            $id = $this->extractSpreadsheetId($raw);

            if ($raw !== null && trim($raw) !== '' && $id === null) {
                return back()
                    ->withInput()
                    ->with('error', GoogleSheetSettings::REPORT_LABELS[$type] . ': that is not a valid spreadsheet ID or Google Sheets URL.');
            }

            $previous = GoogleSheetSettings::savedSpreadsheetId($type);

            if ($id === null) {
                // Cleared on purpose: drop the override so the value
                // falls back to .env again.
                if ($previous !== null) {
                    Setting::forgetKey(GoogleSheetSettings::settingKey($type));
                    $changed[] = GoogleSheetSettings::REPORT_LABELS[$type] . ' (cleared)';
                }

                continue;
            }

            if ($previous !== $id) {
                Setting::put(GoogleSheetSettings::settingKey($type), $id);
                $changed[] = GoogleSheetSettings::REPORT_LABELS[$type];
            }
        }

        if ($changed === []) {
            return back()->with('success', 'No changes to save.');
        }

        Logs::create([
            'type' => 'Google Sheet Settings',
            'message' => 'Updated Google Sheet IDs: ' . implode(', ', $changed),
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Google Sheet settings saved: ' . implode(', ', $changed) . '.');
    }

    /**
     * Stores an uploaded service-account JSON key and points the export
     * at it. Validated as a real service-account key before it replaces
     * whatever is in place.
     */
    public function uploadCredentials(Request $request)
    {
        $request->validate([
            'credentials' => ['required', 'file', 'max:64'],
        ], [], ['credentials' => 'credentials file']);

        $contents = file_get_contents($request->file('credentials')->getRealPath());

        try {
            $json = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return back()->with('error', 'That file is not valid JSON.');
        }

        if (!is_array($json) || ($json['type'] ?? null) !== 'service_account' || empty($json['client_email']) || empty($json['private_key'])) {
            return back()->with('error', 'That is not a Google service-account key file (it needs type "service_account", client_email and private_key).');
        }

        $path = $this->credentialsStoragePath();

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $contents);
        @chmod($path, 0600);

        Setting::put(GoogleSheetSettings::CREDENTIALS_KEY, $path);

        Logs::create([
            'type' => 'Google Sheet Settings',
            'message' => 'Uploaded Google service-account credentials (' . $json['client_email'] . ')',
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Credentials saved. Share each spreadsheet with ' . $json['client_email'] . ' as an Editor.');
    }

    /**
     * Opens the configured spreadsheet for one report type with the
     * configured credentials and reports back what happened - the
     * fastest way to tell a wrong ID from an unshared sheet.
     */
    public function test(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', 'string', 'in:' . implode(',', GoogleSheetSettings::reportTypes())],
        ]);

        $label = GoogleSheetSettings::REPORT_LABELS[$validated['report_type']];

        try {
            $title = (new GoogleSheetExportService($validated['report_type']))->checkAccess();
        } catch (Throwable $e) {
            return back()->with('error', $label . ' test failed: ' . $this->readableError($e));
        }

        return back()->with('success', $label . ' is connected to "' . $title . '".');
    }

    /**
     * Accepts a bare ID or any Google Sheets URL containing
     * /spreadsheets/d/{id}/. Returns null for empty input or anything
     * that is not a plausible spreadsheet ID.
     */
    private function extractSpreadsheetId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#/spreadsheets/d/([A-Za-z0-9\-_]+)#', $value, $matches)) {
            $value = $matches[1];
        }

        return preg_match('/^[A-Za-z0-9\-_]{20,}$/', $value) ? $value : null;
    }

    /**
     * Google's client exceptions carry a whole JSON body; the admin only
     * needs the sentence.
     */
    private function readableError(Throwable $e): string
    {
        $message = $e->getMessage();

        $decoded = json_decode($message, true);

        if (is_array($decoded) && isset($decoded['error']['message'])) {
            return $decoded['error']['message'];
        }

        return mb_strimwidth(trim(preg_replace('/\s+/', ' ', $message)), 0, 300, '...');
    }
}
