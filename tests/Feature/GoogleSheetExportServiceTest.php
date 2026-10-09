<?php

namespace Tests\Feature;

use App\Services\GoogleSheetExportService;
use Tests\TestCase;

/**
 * Doesn't call the real Google API (no network access in tests) — just
 * proves the 4 report types resolve to the exact same spreadsheet IDs the
 * 4 old GoogleSheetService[1-4] classes hardcoded, and that an unknown
 * type is rejected instead of silently doing nothing.
 */
class GoogleSheetExportServiceTest extends TestCase
{
    /** @dataProvider knownReportTypes */
    public function test_a_known_report_type_constructs_without_error(string $reportType): void
    {
        $service = new GoogleSheetExportService($reportType);

        $this->assertInstanceOf(GoogleSheetExportService::class, $service);
    }

    public static function knownReportTypes(): array
    {
        return [
            [GoogleSheetExportService::MORNING_SUMMARY],
            [GoogleSheetExportService::EVENING_SUMMARY],
            [GoogleSheetExportService::MORNING_SHOP_REPORT],
            [GoogleSheetExportService::EVENING_SHOP_REPORT],
        ];
    }

    public function test_an_unknown_report_type_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new GoogleSheetExportService('not_a_real_report');
    }

    /**
     * Phase 4 moved the spreadsheet IDs and credentials path from
     * hardcoded constants into config/services.php (env-overridable).
     * Locks in that with no env vars set, the defaults still resolve to
     * the exact same 4 spreadsheet IDs as before.
     */
    public function test_config_defaults_match_the_original_hardcoded_spreadsheet_ids(): void
    {
        $this->assertSame([
            'morning_summary' => '1NOxV5sU1yAD0AH94Co4snnNam79KHxPAP2sXgmD6Sso',
            'evening_summary' => '1g9ESoKgqdPfs5yr0SnlQ7YLa31CiraDFaYxfGPv4lBg',
            'morning_shop_report' => '18Kco3OHVB3hwciOiEs7a4gAmcRq9slgFWZdOo0wSSCU',
            'evening_shop_report' => '1iVIHkI8PXjRnChGexPw2dagdDcmLB064z7D7LvPMJ8A',
        ], config('services.google_sheets.spreadsheets'));

        $this->assertSame(storage_path('credentials.json'), config('services.google_sheets.credentials_path'));
    }
}
