<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the OrderAdmin role's cart/order flow,
 * written while wiring it onto CartService/OrderService in Phase 3.
 * OrderAdmin has none of Shop's or Rep's extra guards — no time window,
 * no zero-total check, no duplicate-submission guard, no access gate —
 * only the shared core (period-allowed, mismatched-items, same-day-
 * duplicate checks).
 */
class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    public function test_add_to_cart_prices_by_the_shops_tier(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create(['price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '15.50']);

        $this->post('/order-admin/create-order-add-to-cart', [
            'qty' => 3,
            'branch_code' => $shop->branch_code,
            'item_number' => $product->item_number,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'item_number' => $product->item_number,
            'shop_bc_number' => $shop->branch_code,
            'price' => 15.50,
        ]);
    }

    public function test_create_order_succeeds_outside_the_8am_to_4pm_window(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(22, 0));
        $this->actingAsAdmin();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/order-admin/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '250',
            'note' => '',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'shop' => $shop->branch_code,
            'shop_id' => $shop->id,
            'status' => 'Pending',
        ]);
    }

    public function test_create_order_with_zero_total_still_succeeds_unlike_shop(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/order-admin/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '0',
            'note' => '',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['shop' => $shop->branch_code, 'total_price' => 0]);
    }

    public function test_create_order_rejects_a_second_order_for_the_same_period_same_day(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/order-admin/cart/order-process', ['shop' => $shop->branch_code, 'order_time' => 'Morning', 'total' => '100', 'note' => '']);

        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);
        $this->post('/order-admin/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '100',
            'note' => '',
        ])->assertSessionHas('error', 'Order already created for this time period in this shop.');

        $this->assertDatabaseCount('orders', 1);
    }
}
