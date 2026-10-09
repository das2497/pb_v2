<?php

namespace Tests\Feature\Rep;

use App\Models\Cart;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the Rep role's cart/order flow, written
 * while wiring it onto CartService/OrderService in Phase 3. Rep's real
 * rules genuinely differ from Shop's (no time-window enforcement, an
 * access-off gate, a cart-empty check, a one-time form-token guard
 * instead of Shop's 2-second Cache guard) — these tests lock in Rep's
 * actual behavior, not Shop's.
 */
class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function actingAsRep(): Rep
    {
        $rep = Rep::factory()->create(['access' => 'on']);
        $this->actingAs(User::factory()->create(['email' => $rep->email, 'role' => 'rep']));
        return $rep;
    }

    public function test_add_to_cart_prices_by_the_shops_tier(): void
    {
        $this->actingAsRep();
        $shop = Shops::factory()->create(['price_range' => 'PB MRP']);
        $product = Products::factory()->create(['mrp' => '42.00']);

        $this->post('/rep/add-to-cart', [
            'qty' => 2,
            'branch_code' => $shop->branch_code,
            'item_number' => $product->item_number,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('carts', [
            'item_number' => $product->item_number,
            'shop_bc_number' => $shop->branch_code,
            'price' => 42.00,
        ]);
    }

    public function test_add_all_to_cart_requires_a_valid_form_token(): void
    {
        $this->actingAsRep();
        $shop = Shops::factory()->create();
        $product = Products::factory()->create();

        // Load /rep/create-order first so a form_token is issued into the session.
        $this->get('/rep/create-order?shop=' . $shop->branch_code);

        $this->post('/rep/add-to-cart-all', [
            'form_token' => 'not-the-real-token',
            'selected_items' => [$product->item_number],
            'qty' => [$product->item_number => 2],
            'branch_code' => $shop->branch_code,
        ])->assertSessionHasErrors('error');

        $this->assertDatabaseMissing('carts', ['item_number' => $product->item_number]);
    }

    public function test_create_order_is_blocked_when_rep_access_is_off(): void
    {
        $rep = Rep::factory()->create(['access' => 'off']);
        $this->actingAs(User::factory()->create(['email' => $rep->email, 'role' => 'rep']));
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/rep/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '100',
        ])->assertSessionHas('error', 'You do not have access to create orders.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_create_order_is_blocked_when_the_cart_is_empty(): void
    {
        $this->actingAsRep();
        $shop = Shops::factory()->create();

        $this->post('/rep/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '100',
        ])->assertSessionHas('error', 'Cart is empty');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_create_order_succeeds_outside_the_8am_to_4pm_window_unlike_shop(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(21, 0));
        $this->actingAsRep();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/rep/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '100',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'shop' => $shop->branch_code,
            'status' => 'Pending',
            'shop_id' => $shop->id,
        ]);
    }

    public function test_create_order_logs_a_rep_specific_message(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));
        $this->actingAsRep();
        $shop = Shops::factory()->create();
        Cart::factory()->create(['shop_bc_number' => $shop->branch_code, 'order_number' => null]);

        $this->post('/rep/cart/order-process', [
            'shop' => $shop->branch_code,
            'order_time' => 'Morning',
            'total' => '100',
        ]);

        $this->assertDatabaseHas('logs', [
            'type' => 'Create Order',
        ]);
        $log = \App\Models\Logs::where('type', 'Create Order')->first();
        $this->assertStringContainsString('Rep created', $log->message);
    }
}
