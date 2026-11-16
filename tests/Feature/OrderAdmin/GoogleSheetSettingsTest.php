<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Setting;
use App\Services\GoogleSheetExportService;
use App\Services\GoogleSheetSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Order Admin > Google Sheets page writes `settings` rows; these
 * cover the resolution rules around them, not the Google API itself
 * (no network access in tests).
 */
class GoogleSheetSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_falls_back_to_config_when_nothing_is_saved_in_the_panel(): void
    {
        config(['services.google_sheets.spreadsheets.morning_summary' => 'from-env-aaaaaaaaaaaaaaaaaaaa']);

        $this->assertNull(GoogleSheetSettings::savedSpreadsheetId(GoogleSheetExportService::MORNING_SUMMARY));
        $this->assertSame(
            'from-env-aaaaaaaaaaaaaaaaaaaa',
            GoogleSheetSettings::spreadsheetId(GoogleSheetExportService::MORNING_SUMMARY)
        );
    }

    public function test_a_saved_setting_wins_over_config(): void
    {
        config(['services.google_sheets.spreadsheets.morning_summary' => 'from-env-aaaaaaaaaaaaaaaaaaaa']);

        Setting::put(GoogleSheetSettings::settingKey(GoogleSheetExportService::MORNING_SUMMARY), 'from-panel-bbbbbbbbbbbbbbbb');

        $this->assertSame(
            'from-panel-bbbbbbbbbbbbbbbb',
            GoogleSheetSettings::spreadsheetId(GoogleSheetExportService::MORNING_SUMMARY)
        );
    }

    /**
     * Clearing the field in the panel must restore the .env value rather
     * than leave the report pointing at nothing.
     */
    public function test_clearing_a_saved_setting_falls_back_to_config_again(): void
    {
        config(['services.google_sheets.spreadsheets.morning_summary' => 'from-env-aaaaaaaaaaaaaaaaaaaa']);

        $key = GoogleSheetSettings::settingKey(GoogleSheetExportService::MORNING_SUMMARY);
        Setting::put($key, 'from-panel-bbbbbbbbbbbbbbbb');
        Setting::forgetKey($key);

        $this->assertSame(
            'from-env-aaaaaaaaaaaaaaaaaaaa',
            GoogleSheetSettings::spreadsheetId(GoogleSheetExportService::MORNING_SUMMARY)
        );
    }

    public function test_a_report_with_neither_a_setting_nor_config_resolves_to_null(): void
    {
        config(['services.google_sheets.spreadsheets.beverage_shop_report' => null]);

        $this->assertNull(GoogleSheetSettings::spreadsheetId(GoogleSheetExportService::BEVERAGE_SHOP_REPORT));
    }

    public function test_every_report_type_the_panel_lists_is_a_known_spreadsheet_key(): void
    {
        $configured = array_keys(config('services.google_sheets.spreadsheets'));

        $this->assertSame($configured, GoogleSheetSettings::reportTypes());
    }
}
