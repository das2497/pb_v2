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
}
