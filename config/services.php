<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Sheets export (app/Services/GoogleSheetExportService.php)
    |--------------------------------------------------------------------------
    |
    | The service-account credentials file and the 4 report spreadsheet IDs
    | used by the OrderAdmin "refresh" buttons. Defaults match what was
    | hardcoded before Phase 4 — unset env vars reproduce the exact same
    | behavior.
    |
    */

    'google_sheets' => [
        'credentials_path' => env('GOOGLE_SHEETS_CREDENTIALS_PATH', storage_path('credentials.json')),
        'spreadsheets' => [
            'morning_summary' => env('GOOGLE_SHEET_MORNING_SUMMARY_ID', '1NOxV5sU1yAD0AH94Co4snnNam79KHxPAP2sXgmD6Sso'),
            'evening_summary' => env('GOOGLE_SHEET_EVENING_SUMMARY_ID', '1g9ESoKgqdPfs5yr0SnlQ7YLa31CiraDFaYxfGPv4lBg'),
            'morning_shop_report' => env('GOOGLE_SHEET_MORNING_SHOP_REPORT_ID', '18Kco3OHVB3hwciOiEs7a4gAmcRq9slgFWZdOo0wSSCU'),
            'evening_shop_report' => env('GOOGLE_SHEET_EVENING_SHOP_REPORT_ID', '1iVIHkI8PXjRnChGexPw2dagdDcmLB064z7D7LvPMJ8A'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenRouter (app/Services/AIQueryService.php — the AI chat assistant)
    |--------------------------------------------------------------------------
    */

    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
    ],

];
