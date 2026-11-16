<?php

namespace App\Services;

use App\Models\Setting;
use Throwable;

/**
 * Resolves the Google Sheets export's configuration, with the Order
 * Admin panel's saved settings taking precedence over config/env.
 *
 * Order of precedence for every value:
 *   1. the settings row written from Order Admin > Google Sheets
 *   2. config/services.php (i.e. the .env vars / hardcoded defaults)
 *
 * So nothing changes for an installation that never opens the settings
 * page, and clearing a field in the panel falls back to env again
 * instead of leaving a half-configured export.
 */
class GoogleSheetSettings
{
    /** Report type => human label, in the order the settings page lists them. */
    public const REPORT_LABELS = [
        GoogleSheetExportService::MORNING_SUMMARY => 'Morning Summary',
        GoogleSheetExportService::EVENING_SUMMARY => 'Evening Summary',
        GoogleSheetExportService::MORNING_SHOP_REPORT => 'Morning Shop Report',
        GoogleSheetExportService::EVENING_SHOP_REPORT => 'Evening Shop Report',
        GoogleSheetExportService::BEVERAGE_SHOP_REPORT => 'Beverage Shop Report',
    ];

    public const SPREADSHEET_KEY_PREFIX = 'google_sheets.spreadsheets.';
    public const CREDENTIALS_KEY = 'google_sheets.credentials_path';

    public static function reportTypes(): array
    {
        return array_keys(self::REPORT_LABELS);
    }

    public static function settingKey(string $reportType): string
    {
        return self::SPREADSHEET_KEY_PREFIX . $reportType;
    }

    /**
     * The spreadsheet ID in use for a report type, or null when neither
     * the panel nor env has one (the beverage report's out-of-the-box
     * state).
     */
    public static function spreadsheetId(string $reportType): ?string
    {
        $fromConfig = config('services.google_sheets.spreadsheets.' . $reportType);

        $value = Setting::get(self::settingKey($reportType), $fromConfig);

        return ($value === null || $value === '') ? null : $value;
    }

    /** Only the ID saved in the panel, for pre-filling the form. */
    public static function savedSpreadsheetId(string $reportType): ?string
    {
        return Setting::get(self::settingKey($reportType));
    }

    public static function credentialsPath(): string
    {
        return Setting::get(self::CREDENTIALS_KEY, config('services.google_sheets.credentials_path'));
    }

    public static function credentialsExist(): bool
    {
        $path = self::credentialsPath();

        return is_string($path) && $path !== '' && is_file($path);
    }

    /**
     * The service account's own details, for showing on the settings
     * page which address each spreadsheet has to be shared with.
     * Returns null when no readable/valid credentials file is in place.
     */
    public static function credentialsInfo(): ?array
    {
        if (!self::credentialsExist()) {
            return null;
        }

        try {
            $json = json_decode(file_get_contents(self::credentialsPath()), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            return null;
        }

        if (!is_array($json) || !isset($json['client_email'])) {
            return null;
        }

        return [
            'client_email' => $json['client_email'],
            'project_id' => $json['project_id'] ?? null,
            'type' => $json['type'] ?? null,
        ];
    }
}
