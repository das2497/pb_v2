<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the 2026-10-11 fix: the report used to default to a hardcoded
 * 'ROUTE 1' route that doesn't exist in real data (actual route names are
 * like "Route 1-1"), so a fresh visit with no filters touched always
 * rendered a completely empty table. "No route selected" now means "all
 * routes" instead of that nonexistent default.
 */
class ExportReportTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    private function placeCompletedOrder(Shops $shop, Products $product, int $qty): void
    {
        $order = Orders::create([
            'unique_id' => uniqid(),
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'total_price' => $qty * 10,
            'time_period' => 'Morning',
            'status' => 'Complete',
        ]);

        Cart::create([
            'item_number' => $product->item_number,
            'qty' => $qty,
            'price' => 10,
            'shop_bc_number' => $shop->branch_code,
            'order_number' => $order->unique_id,
        ]);
    }

    public function test_no_route_selected_includes_shops_from_every_route(): void
    {
        $this->actingAsAdmin();
        $category = Product_category::factory()->create(['category' => 'TEST CATEGORY']);
        $product = Products::factory()->create(['category' => $category->category]);

        $shopA = Shops::factory()->create(['morning_route' => 'Route A']);
        $shopB = Shops::factory()->create(['morning_route' => 'Route B']);
        $this->placeCompletedOrder($shopA, $product, 5);
        $this->placeCompletedOrder($shopB, $product, 7);

        $response = $this->get('/order-admin/export-report?' . http_build_query([
            'categories' => [$category->category],
            'state' => 'Complete',
            'time_period' => 'Morning',
        ]));

        $response->assertOk();
        $response->assertSee($shopA->name);
        $response->assertSee($shopB->name);
    }

    public function test_selecting_a_route_restricts_to_that_routes_shops(): void
    {
        $this->actingAsAdmin();
        $category = Product_category::factory()->create(['category' => 'TEST CATEGORY']);
        $product = Products::factory()->create(['category' => $category->category]);

        $shopA = Shops::factory()->create(['morning_route' => 'Route A']);
        $shopB = Shops::factory()->create(['morning_route' => 'Route B']);
        $this->placeCompletedOrder($shopA, $product, 5);
        $this->placeCompletedOrder($shopB, $product, 7);

        $response = $this->get('/order-admin/export-report?' . http_build_query([
            'categories' => [$category->category],
            'routes' => ['Route A'],
            'state' => 'Complete',
            'time_period' => 'Morning',
        ]));

        $response->assertOk();
        $response->assertSee($shopA->name);
        $response->assertDontSee($shopB->name);
    }

    public function test_no_category_selected_shows_no_products(): void
    {
        $this->actingAsAdmin();
        $product = Products::factory()->create(['category' => 'CAKE ITEMS']);
        $shop = Shops::factory()->create(['morning_route' => 'Route A']);
        $this->placeCompletedOrder($shop, $product, 5);

        $response = $this->get('/order-admin/export-report?' . http_build_query([
            'state' => 'Complete',
            'time_period' => 'Morning',
        ]));

        $response->assertOk();
        $response->assertDontSee($product->item_number);
    }
}
