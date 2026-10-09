<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the item quantity-update/delete methods on
 * Pending_orders, Processing_orders and Under_review_orders, written
 * before wiring them onto CartService. All three share byte-identical
 * core logic (adjust orders.total_price by the price delta, then update
 * or delete the cart row) but only Pending_orders logs the mutation —
 * Processing_orders and Under_review_orders don't import Logs at all.
 */
class ItemEditTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    private function makeOrderWithCartItem(string $status): array
    {
        $shop = Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00']);
        $order = Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => $status]);
        Cart::factory()->create(['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 2]);

        return [$shop, $product, $order];
    }

    public function test_pending_update_adjusts_total_price_cart_and_logs(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Pending');

        $this->post('/order-admin/pending-orders-update', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 5,
            'remarke' => 'new remark',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 5, 'remarke' => 'new remark']);
        $this->assertDatabaseHas('logs', ['type' => 'Update item pending Order']);
    }

    public function test_pending_delete_adjusts_total_price_removes_cart_row_and_logs(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Pending');

        $this->post('/order-admin/pending-orders-delete', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number]);
        $this->assertDatabaseHas('logs', ['type' => 'Delete item pending Order']);
    }

    public function test_processing_update_adjusts_total_price_and_cart_without_logging(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Processing');

        $this->post('/order-admin/processing-orders-update', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 5,
            'remarke' => 'new remark',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 5]);
        $this->assertSame(0, Logs::count());
    }

    public function test_processing_delete_adjusts_total_price_and_removes_cart_row_without_logging(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Processing');

        $this->post('/order-admin/processing-orders-delete', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number]);
        $this->assertSame(0, Logs::count());
    }

    public function test_under_review_update_adjusts_total_price_and_cart_without_logging(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Under Review');

        $this->post('/order-admin/under-review-orders-update', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 5,
            'remarke' => 'new remark',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 5]);
        $this->assertSame(0, Logs::count());
    }

    public function test_under_review_delete_adjusts_total_price_and_removes_cart_row_without_logging(): void
    {
        $this->actingAsAdmin();
        [$shop, $product, $order] = $this->makeOrderWithCartItem('Under Review');

        $this->post('/order-admin/under-review-orders-delete', [
            'shop' => 'BC-1',
            'item_number' => $product->item_number,
            'order_number' => 'ORD-1',
            'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number]);
        $this->assertSame(0, Logs::count());
    }

    public function test_under_review_delete_order_removes_order_and_its_cart_items(): void
    {
        $this->actingAsAdmin();
        $this->makeOrderWithCartItem('Under Review');

        $this->post('/order-admin/under-review-orders-delete/ORD-1')
            ->assertSessionHas('success', 'Order Deleted Successfully');

        $this->assertDatabaseMissing('orders', ['unique_id' => 'ORD-1']);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
    }
}
