<?php
// app/Services/GoogleSheetService.php

namespace App\Services;

use Google_Client;
use Google_Service_Sheets;

class GoogleSheetService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(storage_path('credentials.json'));
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $this->service = new Google_Service_Sheets($this->client);

        // Replace with your Google Sheet ID
        $this->spreadsheetId = '1NOxV5sU1yAD0AH94Co4snnNam79KHxPAP2sXgmD6Sso';
    }

    public function readSheet($range)
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues();
    }

    public function insertRow($range, $values)
    {
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => [$values]
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];
        return $this->service->spreadsheets_values->append($this->spreadsheetId, $range, $body, $params);
    }

    public function updateRow($range, $values)
    {
        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];
        return $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);
    }

    public function deleteRow($sheetId, $rowIndex)
    {
        $requests = [
            new \Google_Service_Sheets_Request([
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'dimension' => 'ROWS',
                        'startIndex' => $rowIndex,
                        'endIndex' => $rowIndex + 1
                    ]
                ]
            ])
        ];

        $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        return $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
    }
}
