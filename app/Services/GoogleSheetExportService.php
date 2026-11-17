<?php

namespace App\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_ValueRange;

/**
 * Replaces the 4 byte-identical GoogleSheetService[1-4] classes, which
 * differed only in a hardcoded spreadsheet ID. The 4 report types this
 * app exports (morning/evening order summary, morning/evening shop
 * report) are now one parameterized class.
 *
 * The spreadsheet IDs and credentials path are config-driven
 * (config/services.php, env-overridable) as of Phase 4 — the config
 * defaults reproduce the exact values that used to be hardcoded here.
 */
class GoogleSheetExportService
{
    public const MORNING_SUMMARY = 'morning_summary';
    public const EVENING_SUMMARY = 'evening_summary';
    public const MORNING_SHOP_REPORT = 'morning_shop_report';
    public const EVENING_SHOP_REPORT = 'evening_shop_report';
    public const BEVERAGE_SHOP_REPORT = 'beverage_shop_report';

    protected Google_Service_Sheets $service;
    protected string $spreadsheetId;

    /** @var string[]|null Lazily fetched tab titles of $spreadsheetId. */
    protected ?array $sheetTitles = null;

    public function __construct(string $reportType)
    {
        if (!array_key_exists($reportType, config('services.google_sheets.spreadsheets'))) {
            throw new \InvalidArgumentException("Unknown Google Sheet report type: {$reportType}");
        }

        // Panel settings first, config/env second - see GoogleSheetSettings.
        $spreadsheetId = GoogleSheetSettings::spreadsheetId($reportType);

        // A known-but-unconfigured ID (a report whose spreadsheet has
        // never been set in the panel or env, e.g. the beverage shop
        // report out of the box) is its own failure: without this the
        // Google API would be called with an empty ID.
        if (empty($spreadsheetId)) {
            throw new \RuntimeException("No spreadsheet ID configured for Google Sheet report type: {$reportType}");
        }

        $credentialsPath = GoogleSheetSettings::credentialsPath();

        if (!is_string($credentialsPath) || !is_file($credentialsPath)) {
            throw new \RuntimeException('Google service-account credentials file is missing: ' . $credentialsPath);
        }

        $client = new Google_Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $this->service = new Google_Service_Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    /**
     * Minimal read against the configured spreadsheet, for the settings
     * page's "Test" button: proves the credentials can open that exact
     * sheet. Returns the spreadsheet's title.
     */
    public function checkAccess(): string
    {
        $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);

        return $spreadsheet->getProperties()->getTitle();
    }

    /** Tab titles of the configured spreadsheet, fetched once per instance. */
    protected function sheetTitles(): array
    {
        if ($this->sheetTitles === null) {
            $titles = [];

            foreach ($this->service->spreadsheets->get($this->spreadsheetId)->getSheets() as $sheet) {
                $titles[] = $sheet->getProperties()->getTitle();
            }

            $this->sheetTitles = $titles;
        }

        return $this->sheetTitles;
    }

    /**
     * The callers hardcode 'Sheet1!A1', but a spreadsheet's first tab is
     * only called "Sheet1" by default - rename it, or create the sheet in
     * a non-English locale, and the API rejects the range with
     * "Unable to parse range". So a tab name that does not exist in the
     * spreadsheet (or a range given with no tab at all) is resolved to
     * the first tab, which is what every export here writes to.
     */
    public function resolveRange(string $range): string
    {
        $titles = $this->sheetTitles();

        if ($titles === []) {
            return $range;
        }

        $separator = strrpos($range, '!');
        $tab = $separator === false ? null : trim(substr($range, 0, $separator), "'");
        $cells = $separator === false ? $range : substr($range, $separator + 1);

        if ($tab !== null && in_array($tab, $titles, true)) {
            return $range;
        }

        $title = $titles[0];
        $quoted = preg_match('/^[A-Za-z0-9_]+$/', $title)
            ? $title
            : "'" . str_replace("'", "''", $title) . "'";

        return $cells === '' ? $quoted : $quoted . '!' . $cells;
    }

    public function readSheet($range)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->resolveRange($range));
        return $response->getValues();
    }

    public function insertRow($range, $values)
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [$values],
        ]);

        return $this->service->spreadsheets_values->append($this->spreadsheetId, $this->resolveRange($range), $body, [
            'valueInputOption' => 'RAW',
        ]);
    }

    public function updateRow($range, $values)
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);

        return $this->service->spreadsheets_values->update($this->spreadsheetId, $this->resolveRange($range), $body, [
            'valueInputOption' => 'RAW',
        ]);
    }

    public function deleteRow($sheetId, $rowIndex)
    {
        $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                new Google_Service_Sheets_Request([
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'ROWS',
                            'startIndex' => $rowIndex,
                            'endIndex' => $rowIndex + 1,
                        ],
                    ],
                ]),
            ],
        ]);

        return $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }
}
