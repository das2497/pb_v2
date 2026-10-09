<?php

namespace Tests\Feature\OrderAdmin;

use App\Http\Controllers\OrderAdmin\GoogleSheetController;
use App\Http\Controllers\OrderAdmin\GoogleSheetController2;
use App\Http\Controllers\OrderAdmin\GoogleSheetController3;
use App\Http\Controllers\OrderAdmin\GoogleSheetController4;
use App\Services\GoogleSheetExportService;
use ReflectionClass;
use Tests\TestCase;

/**
 * Verifies each of the 4 OrderAdmin Google Sheet refresh controllers now
 * builds the shared GoogleSheetExportService targeting the exact same
 * spreadsheet the old, now-deleted GoogleSheetService[1-4] classes each
 * hardcoded. Doesn't call the real Google API (no network access in
 * tests) — constructing the service only reads the local
 * storage/credentials.json, same as GoogleSheetExportServiceTest relies
 * on.
 */
class GoogleSheetWiringTest extends TestCase
{
    /** @dataProvider controllersAndTheirReportType */
    public function test_controller_targets_its_correct_report_types_spreadsheet(string $controllerClass, string $reportType): void
    {
        $controller = app($controllerClass);

        $serviceProperty = new ReflectionClass($controller);
        $service = $serviceProperty->getProperty('googleSheetService');
        $service->setAccessible(true);
        $resolvedService = $service->getValue($controller);

        $this->assertInstanceOf(GoogleSheetExportService::class, $resolvedService);
        $this->assertSame(
            $this->spreadsheetIdOf(new GoogleSheetExportService($reportType)),
            $this->spreadsheetIdOf($resolvedService)
        );
    }

    public static function controllersAndTheirReportType(): array
    {
        return [
            'morning summary' => [GoogleSheetController::class, GoogleSheetExportService::MORNING_SUMMARY],
            'evening summary' => [GoogleSheetController2::class, GoogleSheetExportService::EVENING_SUMMARY],
            'morning shop report' => [GoogleSheetController3::class, GoogleSheetExportService::MORNING_SHOP_REPORT],
            'evening shop report' => [GoogleSheetController4::class, GoogleSheetExportService::EVENING_SHOP_REPORT],
        ];
    }

    private function spreadsheetIdOf(GoogleSheetExportService $service): string
    {
        $property = (new ReflectionClass($service))->getProperty('spreadsheetId');
        $property->setAccessible(true);

        return $property->getValue($service);
    }
}
