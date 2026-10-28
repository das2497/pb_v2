<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The 4 Final Report pages had the same N+1 bug as the Shop Report pages
 * did before that fix: a SUM() query per product x route table cell run
 * directly in the Blade view, pagination that hid everything past page 1,
 * a fixed Processing-status/today-only filter with no way to change it,
 * and full-screen variants with a fixed small height instead of filling
 * the screen. This brings Final Report up to the same pattern as Shop
 * Report: one aggregated route => item => qty map built per request,
 * every product visible via scroll, date/state filters on every page, and
 * an Excel export that honors those filters.
 */
class FinalReportTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    private function makeRouteWithShops(string $time, string $type, int $shopCount): array
    {
        $route = Routes::factory()->create(['type' => $type, 'time' => $time]);
        $column = $time === 'Morning' ? 'morning_route' : 'evening_route';

        $shops = Shops::factory()->count($shopCount)->create([$column => $route->name]);

        return [$route, $shops];
    }

    /**
     * Several routes (spread across Normal/Special/PBD) each with one shop,
     * so the old per-cell query pattern (one SUM() per product x route)
     * actually multiplies enough to be distinguishable from a flat,
     * pre-aggregated query count.
     */
    private function makeManyRoutesWithShops(string $time, int $routeCount): \Illuminate\Support\Collection
    {
        $column = $time === 'Morning' ? 'morning_route' : 'evening_route';
        $types = ['Normal', 'Special', 'PBD'];

        return collect(range(1, $routeCount))->map(function ($i) use ($time, $column, $types) {
            $route = Routes::factory()->create(['type' => $types[$i % 3], 'time' => $time]);
            $shop = Shops::factory()->create([$column => $route->name]);

            return ['route' => $route, 'shop' => $shop];
        });
    }

    private function placeOrder(Shops $shop, Products $product, int $qty, string $timePeriod, string $status): void
    {
        $order = Orders::create([
            'unique_id' => uniqid('', true),
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'total_price' => $qty * 10,
            'time_period' => $timePeriod,
            'status' => $status,
        ]);

        Cart::create([
            'item_number' => $product->item_number,
            'qty' => $qty,
            'price' => 10,
            'remarke' => 'test remark',
            'shop_bc_number' => $shop->branch_code,
            'order_number' => $order->unique_id,
        ]);
    }

    public function test_morning_report_sums_quantities_across_shops_on_a_route_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [$route, $shops] = $this->makeRouteWithShops('Morning', 'Normal', 3);
        $product = Products::factory()->create();

        // Two different shops on the SAME route, same product: the route
        // column must show the sum, not just one shop's qty.
        $this->placeOrder($shops[0], $product, 4, 'Morning', 'Processing');
        $this->placeOrder($shops[1], $product, 6, 'Morning', 'Processing');

        $response = $this->get('/order-admin/final-report');

        $response->assertOk();
        $response->assertSeeText('10'); // 4 + 6 summed onto the shared route column
    }

    public function test_evening_report_sums_quantities_across_shops_on_a_route(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'Normal', 3);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 3, 'Evening', 'Processing');
        $this->placeOrder($shops[2], $product, 5, 'Evening', 'Processing');

        $response = $this->get('/order-admin/final-report-evening');

        $response->assertOk();
        $response->assertSeeText('8');
    }

    /**
     * The real regression guard for the N+1 fix: enough distinct routes
     * that the old "one SUM() query per product x route cell" pattern
     * would multiply into dozens of queries, versus a flat, pre-aggregated
     * query count regardless of how many routes/products exist.
     */
    public function test_morning_report_query_count_stays_flat_as_routes_and_products_grow(): void
    {
        $this->actingAsAdmin();
        $entries = $this->makeManyRoutesWithShops('Morning', 9);
        $products = Products::factory()->count(4)->create();

        foreach ($entries as $entry) {
            $this->placeOrder($entry['shop'], $products[0], 1, 'Morning', 'Processing');
        }

        DB::enableQueryLog();
        $response = $this->get('/order-admin/final-report');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(
            15,
            $queryCount,
            "Final report ran {$queryCount} queries for 9 routes x 4 products - should be a flat handful, not one query per cell."
        );
    }

    public function test_evening_report_query_count_stays_flat_as_routes_and_products_grow(): void
    {
        $this->actingAsAdmin();
        $entries = $this->makeManyRoutesWithShops('Evening', 9);
        $products = Products::factory()->count(4)->create();

        foreach ($entries as $entry) {
            $this->placeOrder($entry['shop'], $products[0], 1, 'Evening', 'Processing');
        }

        DB::enableQueryLog();
        $response = $this->get('/order-admin/final-report-evening');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(
            15,
            $queryCount,
            "Evening final report ran {$queryCount} queries for 9 routes x 4 products - should be a flat handful, not one query per cell."
        );
    }

    public function test_morning_full_screen_sums_quantities(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Morning', 'Special', 2);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 9, 'Morning', 'Processing');

        $response = $this->get('/order-admin/final-report-full-screen');

        $response->assertOk();
        $response->assertSeeText('9');
    }

    public function test_evening_full_screen_sums_quantities(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'PBD', 2);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 12, 'Evening', 'Processing');

        $response = $this->get('/order-admin/final-report-evening-full-screen');

        $response->assertOk();
        $response->assertSeeText('12');
    }

    /** @return \Illuminate\Support\Collection<int, Products> */
    private function makeOrderedProducts(int $count): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(
            fn ($i) => Products::factory()->create(['item_number' => sprintf('ITM-%05d', $i)])
        );
    }

    public function test_morning_report_includes_every_product_no_pagination(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Morning', 'Normal', 1);
        $products = $this->makeOrderedProducts(25); // old page size was 20
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 8614, 'Morning', 'Processing');

        $response = $this->get('/order-admin/final-report');
        $response->assertOk();
        $response->assertSeeText($lastProduct->item_number);
        $response->assertSeeText('8614');
    }

    public function test_evening_report_includes_every_product_no_pagination(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'Normal', 1);
        $products = $this->makeOrderedProducts(25); // old page size was 20
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 7325, 'Evening', 'Processing');

        $response = $this->get('/order-admin/final-report-evening');
        $response->assertOk();
        $response->assertSeeText($lastProduct->item_number);
        $response->assertSeeText('7325');
    }

    /**
     * Covers the same filter round-trip bug the shop reports had: the date
     * input and state dropdown must reflect whatever was actually applied,
     * not reset to blank/Processing after every search.
     */
    public function test_morning_report_filters_by_date_and_state_and_echoes_it_back(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Morning', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 6192, 'Morning', 'Complete');
        $default = $this->get('/order-admin/final-report');
        $default->assertOk();
        $default->assertDontSeeText('6192');

        $filtered = $this->get('/order-admin/final-report?date=' . now()->toDateString() . '&state=Complete');
        $filtered->assertOk();
        $filtered->assertSeeText('6192');
        $filtered->assertSee('value="Complete" selected', false);
    }

    public function test_evening_report_filters_by_date_and_state_and_echoes_it_back(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 5283, 'Evening', 'Pending');
        $default = $this->get('/order-admin/final-report-evening');
        $default->assertOk();
        $default->assertDontSeeText('5283');

        $filtered = $this->get('/order-admin/final-report-evening?date=' . now()->toDateString() . '&state=Pending');
        $filtered->assertOk();
        $filtered->assertSeeText('5283');
        $filtered->assertSee('value="Pending" selected', false);
    }

    public function test_morning_full_screen_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Morning', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 4718, 'Morning', 'Complete');
        $default = $this->get('/order-admin/final-report-full-screen');
        $default->assertOk();
        $default->assertDontSeeText('4718');

        $filtered = $this->get('/order-admin/final-report-full-screen?date=' . now()->toDateString() . '&state=Complete');
        $filtered->assertOk();
        $filtered->assertSeeText('4718');
        $filtered->assertSee('value="Complete" selected', false);
    }

    public function test_evening_full_screen_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 3927, 'Evening', 'Complete');
        $default = $this->get('/order-admin/final-report-evening-full-screen');
        $default->assertOk();
        $default->assertDontSeeText('3927');

        $filtered = $this->get('/order-admin/final-report-evening-full-screen?date=' . now()->toDateString() . '&state=Complete');
        $filtered->assertOk();
        $filtered->assertSeeText('3927');
        $filtered->assertSee('value="Complete" selected', false);
    }

    public function test_export_morning_report_includes_every_product_and_honors_filters(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Morning', 'Normal', 1);
        $products = $this->makeOrderedProducts(25);
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 9461, 'Morning', 'Complete');

        $exportDefault = $this->get('/order-admin/final-report/export');
        $exportDefault->assertOk();
        $exportDefault->assertDontSeeText('9461');

        $exportFiltered = $this->get('/order-admin/final-report/export?date=' . now()->toDateString() . '&state=Complete');
        $exportFiltered->assertOk();
        $exportFiltered->assertSeeText($lastProduct->item_number);
        $exportFiltered->assertSeeText('9461');
    }

    public function test_export_evening_report_includes_every_product_and_honors_filters(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteWithShops('Evening', 'Normal', 1);
        $products = $this->makeOrderedProducts(25);
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 8572, 'Evening', 'Complete');

        $exportDefault = $this->get('/order-admin/final-report-evening/export');
        $exportDefault->assertOk();
        $exportDefault->assertDontSeeText('8572');

        $exportFiltered = $this->get('/order-admin/final-report-evening/export?date=' . now()->toDateString() . '&state=Complete');
        $exportFiltered->assertOk();
        $exportFiltered->assertSeeText($lastProduct->item_number);
        $exportFiltered->assertSeeText('8572');
    }
}
