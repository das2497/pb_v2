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

    protected Google_Service_Sheets $service;
    protected string $spreadsheetId;

    public function __construct(string $reportType)
    {
        $spreadsheets = config('services.google_sheets.spreadsheets');

        if (!isset($spreadsheets[$reportType])) {
            throw new \InvalidArgumentException("Unknown Google Sheet report type: {$reportType}");
        }

        $client = new Google_Client();
        $client->setAuthConfig(config('services.google_sheets.credentials_path'));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $this->service = new Google_Service_Sheets($client);
        $this->spreadsheetId = $spreadsheets[$reportType];
    }

    public function readSheet($range)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues();
    }

    public function insertRow($range, $values)
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [$values],
        ]);

        return $this->service->spreadsheets_values->append($this->spreadsheetId, $range, $body, [
            'valueInputOption' => 'RAW',
        ]);
    }

    public function updateRow($range, $values)
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);

        return $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $body, [
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
