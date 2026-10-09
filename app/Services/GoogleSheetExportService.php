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
 * The spreadsheet IDs and credentials path stay hardcoded here for now —
 * moving them into config/services.php + .env is Phase 4 (Integrations
 * Hardening), not this phase.
 */
class GoogleSheetExportService
{
    public const MORNING_SUMMARY = 'morning_summary';
    public const EVENING_SUMMARY = 'evening_summary';
    public const MORNING_SHOP_REPORT = 'morning_shop_report';
    public const EVENING_SHOP_REPORT = 'evening_shop_report';

    private const SPREADSHEETS = [
        self::MORNING_SUMMARY => '1NOxV5sU1yAD0AH94Co4snnNam79KHxPAP2sXgmD6Sso',
        self::EVENING_SUMMARY => '1g9ESoKgqdPfs5yr0SnlQ7YLa31CiraDFaYxfGPv4lBg',
        self::MORNING_SHOP_REPORT => '18Kco3OHVB3hwciOiEs7a4gAmcRq9slgFWZdOo0wSSCU',
        self::EVENING_SHOP_REPORT => '1iVIHkI8PXjRnChGexPw2dagdDcmLB064z7D7LvPMJ8A',
    ];

    protected Google_Service_Sheets $service;
    protected string $spreadsheetId;

    public function __construct(string $reportType)
    {
        if (!isset(self::SPREADSHEETS[$reportType])) {
            throw new \InvalidArgumentException("Unknown Google Sheet report type: {$reportType}");
        }

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('credentials.json'));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $this->service = new Google_Service_Sheets($client);
        $this->spreadsheetId = self::SPREADSHEETS[$reportType];
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
