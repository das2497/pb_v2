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
 * The 6 Shop Report pages used to run a separate DB query per table cell
 * (one query per product x shop, two for the "normal" variants) directly
 * in the Blade view, instead of the pre-built order lookup map the morning
 * report already used. This locks in the fix: correct totals, and a query
 * count that stays flat as the number of shops/products grows instead of
 * multiplying with it.
 */
class ShopReportTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    private function makeRouteAndShops(string $time, string $type, int $count): array
    {
        $route = Routes::factory()->create(['type' => $type, 'time' => $time]);
        $column = $time === 'Morning' ? 'morning_route' : 'evening_route';

        $shops = Shops::factory()->count($count)->create([$column => $route->name]);

        return [$route, $shops];
    }

    private function placeOrder(Shops $shop, Products $product, int $qty, string $timePeriod, string $status, ?string $note = null): void
    {
        $order = Orders::create([
            'unique_id' => uniqid('', true),
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'total_price' => $qty * 10,
            'time_period' => $timePeriod,
            'status' => $status,
            'note' => $note,
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

    /**
     * A blank date field still submits `date=` (an empty string), not an
     * absent key. `$request->filled('date')` guards against that
     * explicitly instead of relying on the framework's global
     * ConvertEmptyStringsToNull middleware to have turned it into null
     * first — so this keeps working even if that middleware is ever
     * removed or the route moves outside its group.
     */
    public function test_search_with_blank_date_field_still_filters_by_today(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 2);
        $products = Products::factory()->count(2)->create();
        // An unusual, multi-digit quantity so it can't coincidentally match
        // a random branch code / phone number fragment elsewhere on the page.
        $this->placeOrder($shops[0], $products[0], 8347, 'Morning', 'Processing');

        // Simulates submitting the Search form with the date input left blank:
        // the browser still sends date= as an empty string, not an absent key.
        $response = $this->get('/order-admin/shop-report?date=&state=Processing');

        $response->assertOk();
        $response->assertSeeText('8347');
    }

    /**
     * The actual bug behind the 2026-10-11 report: index() never passed
     * $currentDate/$state back to the view, so after every Search the date
     * input and state dropdown reset to blank/"Processing" instead of
     * showing what was actually applied — making it look like the filter
     * had no effect even though the underlying query was correct.
     */
    public function test_search_result_echoes_the_applied_filters_back_into_the_form(): void
    {
        $this->actingAsAdmin();
        $this->makeRouteAndShops('Morning', 'Normal', 1);
        Products::factory()->create();

        $response = $this->get('/order-admin/shop-report?date=2026-01-15&state=Complete');

        $response->assertOk();
        $response->assertSee('value="2026-01-15"', false);
        $response->assertSee('value="Complete" selected', false);
    }

    /**
     * 2026-10-12: the evening shop report had no date/state filter at all
     * (hardcoded to today + Processing), unlike the morning report. It now
     * takes the same ?date=&state= filters, echoes them back into the form,
     * and actually changes which orders show.
     */
    public function test_evening_report_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Evening', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 7461, 'Evening', 'Complete');
        // Default (no filter) is Processing, so this Complete order should
        // be invisible until the admin explicitly filters for it.
        $default = $this->get('/order-admin/shop-report-evening');
        $default->assertOk();
        $default->assertDontSeeText('7461');

        $filtered = $this->get('/order-admin/shop-report-evening?date=' . now()->toDateString() . '&state=Complete');
        $filtered->assertOk();
        $filtered->assertSeeText('7461');
        $filtered->assertSee('value="Complete" selected', false);

        // The export endpoint behind the "Export to Excel" button reads the
        // same ?date=&state= filters the page was loaded with.
        $exportDefault = $this->get('/order-admin/shop-report-evening/export');
        $exportDefault->assertOk();
        $exportDefault->assertDontSeeText('7461');

        $exportFiltered = $this->get('/order-admin/shop-report-evening/export?date=' . now()->toDateString() . '&state=Complete');
        $exportFiltered->assertOk();
        $exportFiltered->assertSeeText('7461');
    }

    public function test_morning_report_shows_correct_quantities_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 3);
        $products = Products::factory()->count(3)->create();

        $this->placeOrder($shops[0], $products[0], 5, 'Morning', 'Processing');
        $this->placeOrder($shops[1], $products[1], 7, 'Morning', 'Processing');

        DB::enableQueryLog();
        $response = $this->get('/order-admin/shop-report');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSeeText('5');
        $response->assertSeeText('7');
        $this->assertLessThan(10, $queryCount, 'Morning shop report should not query per table cell.');
    }

    public function test_evening_report_shows_correct_quantities_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Evening', 'Normal', 4);
        $products = Products::factory()->count(4)->create();

        $this->placeOrder($shops[0], $products[0], 3, 'Evening', 'Processing');
        $this->placeOrder($shops[2], $products[2], 9, 'Evening', 'Processing');

        DB::enableQueryLog();
        $response = $this->get('/order-admin/shop-report-evening');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSeeText('3');
        $response->assertSeeText('9');
        $this->assertLessThan(10, $queryCount, 'Evening shop report should not query per table cell.');
    }

    public function test_evening_full_screen_report_shows_correct_quantities_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Evening', 'Normal', 4);
        $products = Products::factory()->count(4)->create();

        $this->placeOrder($shops[1], $products[1], 11, 'Evening', 'Processing');

        DB::enableQueryLog();
        $response = $this->get('/order-admin/shop-report-full-screen-evening');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSeeText('11');
        $this->assertLessThan(10, $queryCount, 'Evening full-screen shop report should not query per table cell.');
    }

    public function test_morning_normal_report_shows_quantity_and_note_word_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 3);
        $products = Products::factory()->count(3)->create();

        $this->placeOrder($shops[0], $products[0], 6, 'Morning', 'Complete', 'hello world extra');

        DB::enableQueryLog();
        $response = $this->get('/order-admin/shop-normal-report');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSeeText('6');
        // Row 0 (first product) should show the first word of that shop's note.
        $response->assertSeeText('hello');
        $this->assertLessThan(12, $queryCount, 'Morning normal shop report should not query per table cell.');
    }

    public function test_morning_normal_full_screen_report_shows_correct_quantities_with_bounded_queries(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 3);
        $products = Products::factory()->count(3)->create();

        $this->placeOrder($shops[2], $products[2], 4, 'Morning', 'Complete');

        DB::enableQueryLog();
        $response = $this->get('/order-admin/shop-report-normal-full-screen');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertSeeText('4');
        $this->assertLessThan(12, $queryCount, 'Morning normal full-screen shop report should not query per table cell.');
    }

    public function test_morning_full_screen_report_shows_correct_quantities(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 3);
        $products = Products::factory()->count(3)->create();

        $this->placeOrder($shops[0], $products[0], 8, 'Morning', 'Processing');

        $response = $this->get('/order-admin/shop-report-full-screen');

        $response->assertOk();
        $response->assertSeeText('8');
    }

    /**
     * 2026-10-12: the full-screen pages had no date/state filter at all
     * (hardcoded to today + a fixed status), unlike their regular-page
     * counterparts. All 3 full-screen pages now accept the same ?date=
     * &state= filters, echo the applied values back into the form like the
     * main morning report does, and actually change which orders show.
     */
    public function test_morning_full_screen_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 8451, 'Morning', 'Complete');
        // Default (no filter) is Processing, so this Complete order should
        // be invisible until the admin explicitly filters for it.
        $default = $this->get('/order-admin/shop-report-full-screen');
        $default->assertOk();
        $default->assertDontSeeText('8451');

        $filtered = $this->get('/order-admin/shop-report-full-screen?date=' . now()->toDateString() . '&state=Complete');
        $filtered->assertOk();
        $filtered->assertSeeText('8451');
        $filtered->assertSee('value="Complete" selected', false);
    }

    public function test_evening_full_screen_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Evening', 'Normal', 1);
        $product = Products::factory()->create();

        $this->placeOrder($shops[0], $product, 6283, 'Evening', 'Pending');
        $default = $this->get('/order-admin/shop-report-full-screen-evening');
        $default->assertOk();
        $default->assertDontSeeText('6283');

        $filtered = $this->get('/order-admin/shop-report-full-screen-evening?date=' . now()->toDateString() . '&state=Pending');
        $filtered->assertOk();
        $filtered->assertSeeText('6283');
        $filtered->assertSee('value="Pending" selected', false);
    }

    public function test_morning_normal_full_screen_filters_by_date_and_state(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 1);
        $product = Products::factory()->create();

        // Default for this page is Complete, so a Processing order should
        // be invisible until the admin filters for it.
        $this->placeOrder($shops[0], $product, 9177, 'Morning', 'Processing');
        $default = $this->get('/order-admin/shop-report-normal-full-screen');
        $default->assertOk();
        $default->assertDontSeeText('9177');

        $filtered = $this->get('/order-admin/shop-report-normal-full-screen?date=' . now()->toDateString() . '&state=Processing');
        $filtered->assertOk();
        $filtered->assertSeeText('9177');
        $filtered->assertSee('value="Processing" selected', false);
    }

    /** @return \Illuminate\Support\Collection<int, Products> */
    private function makeOrderedProducts(int $count): \Illuminate\Support\Collection
    {
        return collect(range(1, $count))->map(
            fn ($i) => Products::factory()->create(['item_number' => sprintf('ITM-%05d', $i)])
        );
    }

    /**
     * 2026-10-12: pagination was removed from every Shop Report page so the
     * full product list is reachable by scrolling the table instead of
     * clicking through pages (the "normal" variants didn't even render
     * pagination links, so anything past page 1 was previously
     * unreachable in the UI at all). Both the live page and the Excel
     * export now show every product.
     */
    public function test_morning_report_includes_every_product_no_pagination(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 1);
        $products = $this->makeOrderedProducts(25); // old page size was 20
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 42, 'Morning', 'Processing');

        $page = $this->get('/order-admin/shop-report');
        $page->assertOk();
        $page->assertSeeText($lastProduct->item_number);
        $page->assertSeeText('42');

        $export = $this->get('/order-admin/shop-report/export');
        $export->assertOk();
        $export->assertSeeText($lastProduct->item_number);
        $export->assertSeeText('42');
    }

    public function test_evening_report_includes_every_product_no_pagination(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Evening', 'Normal', 1);
        $products = $this->makeOrderedProducts(25); // old page size was 20
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 37, 'Evening', 'Processing');

        $page = $this->get('/order-admin/shop-report-evening');
        $page->assertOk();
        $page->assertSeeText($lastProduct->item_number);
        $page->assertSeeText('37');

        $export = $this->get('/order-admin/shop-report-evening/export');
        $export->assertOk();
        $export->assertSeeText($lastProduct->item_number);
        $export->assertSeeText('37');
    }

    public function test_morning_normal_report_includes_every_product_no_pagination(): void
    {
        $this->actingAsAdmin();
        [, $shops] = $this->makeRouteAndShops('Morning', 'Normal', 1);
        $products = $this->makeOrderedProducts(15); // old page size was 10
        $lastProduct = $products->last();

        $this->placeOrder($shops[0], $lastProduct, 19, 'Morning', 'Complete');

        $page = $this->get('/order-admin/shop-normal-report');
        $page->assertOk();
        $page->assertSeeText($lastProduct->item_number);
        $page->assertSeeText('19');

        $export = $this->get('/order-admin/shop-normal-report/export');
        $export->assertOk();
        $export->assertSeeText($lastProduct->item_number);
        $export->assertSeeText('19');
    }
}
