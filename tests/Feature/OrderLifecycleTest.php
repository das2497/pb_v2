<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Shops;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the order status machine
 * (Pending -> Processing -> Complete, plus Cancelled/Default) before the
 * Phase 2 refactor moves these transitions into a single OrderService.
 * These lock in current behavior across the Shop and OrderAdmin
 * controllers so the refactor can be verified against them.
 */
class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_shop_creates_a_pending_order_from_their_cart_within_the_allowed_time_window(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));

        $shop = Shops::factory()->create();
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        Cart::factory()->create([
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
        ]);

        $this->actingAs($user)->post('/shop/cart/order-process', [
            'order_time' => 'Morning',
            'total' => '250',
            'shop' => $shop->branch_code,
            'note' => 'Please deliver before noon',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'shop' => $shop->branch_code,
            'status' => 'Pending',
            'time_period' => 'Morning',
            'total_price' => 250,
        ]);

        $order = Orders::where('shop', $shop->branch_code)->first();
        $this->assertDatabaseHas('carts', [
            'shop_bc_number' => $shop->branch_code,
            'order_number' => $order->unique_id,
        ]);
    }

    public function test_order_creation_is_blocked_outside_the_8am_to_4pm_window(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(20, 0));

        $shop = Shops::factory()->create();
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        Cart::factory()->create([
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
        ]);

        $this->actingAs($user)->post('/shop/cart/order-process', [
            'order_time' => 'Morning',
            'total' => '250',
            'shop' => $shop->branch_code,
            'note' => '',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_shop_cannot_create_a_second_order_for_the_same_period_on_the_same_day(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));

        $shop = Shops::factory()->create();
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        Orders::create([
            'unique_id' => uniqid(),
            'shop' => $shop->branch_code,
            'total_price' => 100,
            'note' => '',
            'time_period' => 'Morning',
            'status' => 'Pending',
        ]);
        Cart::factory()->create([
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
        ]);

        $this->actingAs($user)->post('/shop/cart/order-process', [
            'order_time' => 'Morning',
            'total' => '250',
            'shop' => $shop->branch_code,
            'note' => '',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_order_admin_accepting_a_pending_order_moves_it_to_processing(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);
        $order = Orders::create([
            'unique_id' => uniqid(),
            'shop' => 'BC-0001',
            'total_price' => 300,
            'note' => '',
            'time_period' => 'Morning',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)->post('/order-admin/pending-orders-accept', [
            'order_number' => $order->unique_id,
        ])->assertRedirect('/order-admin/pending-orders');

        $this->assertDatabaseHas('orders', [
            'unique_id' => $order->unique_id,
            'status' => 'Processing',
        ]);
    }

    public function test_processing_transfer_bulk_moves_every_processing_order_to_complete(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-0001', 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Processing']);
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-0002', 'total_price' => 200, 'time_period' => 'Evening', 'status' => 'Processing']);
        Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-0003', 'total_price' => 300, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->actingAs($admin)->get('/order-admin/processing-transfer');

        $this->assertSame(0, Orders::where('status', 'Processing')->count());
        $this->assertSame(2, Orders::where('status', 'Complete')->count());
        $this->assertSame(1, Orders::where('status', 'Pending')->count());
    }

    public function test_shop_can_save_their_cart_as_a_default_standing_order(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0));

        $shop = Shops::factory()->create();
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        Cart::factory()->create([
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
        ]);

        $this->actingAs($user)->post('/shop/cart/order-process', [
            'order_time' => 'Morning',
            'total' => '250',
            'shop' => $shop->branch_code,
            'note' => 'Weekly standing order',
            'default' => 'on',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'shop' => $shop->branch_code,
            'status' => 'Default',
            'time_period' => 'Default',
            'default_period' => 'Morning',
            'default_name' => 'Weekly standing order',
        ]);
    }
}
