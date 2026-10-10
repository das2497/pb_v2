<?php

namespace Tests\Unit\Services;

use App\Models\Products;
use App\Models\Shops;
use App\Services\PricingService;
use PHPUnit\Framework\TestCase;

/**
 * Direct unit coverage for PricingService::priceFor() - no DB, no HTTP.
 * The price_range -> column mapping is already exercised indirectly through
 * Shop/PricingTierTest.php and Services/CartServiceTest.php, but neither
 * covers an unrecognized price_range, which priceFor() silently maps to 0.0
 * rather than erroring - that's the real current behavior, not a gap to fix.
 */
class PricingServiceTest extends TestCase
{
    private function product(): Products
    {
        return new Products([
            'unit_price' => '100.00',
            'mrp' => '120.00',
            'direct_sale_price' => '90.00',
        ]);
    }

    /** @dataProvider priceTiers */
    public function test_price_for_matches_the_shops_tier(string $priceRange, float $expected): void
    {
        $shop = new Shops(['price_range' => $priceRange]);

        $this->assertSame($expected, PricingService::priceFor($this->product(), $shop));
    }

    public static function priceTiers(): array
    {
        return [
            'unit price tier' => ['Unit Price', 100.0],
            'MRP tier' => ['PB MRP', 120.0],
            'direct sale tier' => ['PB Direct Sale Price', 90.0],
        ];
    }

    public function test_an_unrecognized_price_range_falls_back_to_zero(): void
    {
        $shop = new Shops(['price_range' => 'Something Else']);

        $this->assertSame(0.0, PricingService::priceFor($this->product(), $shop));
    }

    public function test_a_null_price_range_falls_back_to_zero(): void
    {
        $shop = new Shops();

        $this->assertSame(0.0, PricingService::priceFor($this->product(), $shop));
    }
}
