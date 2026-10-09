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
 * Characterization tests for store() on Pending_orders_add_item,
 * Processing_orders_add_item and Under_review_orders_add_item, written
 * before wiring them onto CartService. All three share byte-identical
 * core logic (create a cart row against the order, bump total_price)
 * but: only Pending/Processing log the mutation (Under_review doesn't
 * import Logs at all), and Processing's success message is a copy-paste
 * bug that still says "pending order" instead of "processing order" —
 * preserved exactly, not fixed.
 */
class AddItemTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    private function makeOrder(string $status, string $period = 'Morning'): Products
    {
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00', 'order_time' => 'All']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => $period, 'status' => $status]);

        return $product;
    }

    public function test_pending_add_item_creates_cart_row_bumps_total_and_logs(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeOrder('Pending');

        $this->post('/order-admin/pending-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 3, 'price' => 10]);
        $this->assertDatabaseHas('logs', ['type' => 'Add To pending order']);
    }

    public function test_pending_add_item_rejects_a_product_unavailable_for_the_orders_period(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeOrder('Pending');
        $product->update(['order_time' => 'Evening']);

        $this->post('/order-admin/pending-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('error', "This product is not available for this order's time period.");

        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
    }

    public function test_processing_add_item_bumps_total_and_logs_with_pending_wording_bug(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeOrder('Processing');

        $this->post('/order-admin/processing-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Add To processing order']);
    }

    public function test_under_review_add_item_bumps_total_without_logging(): void
    {
        $this->actingAsAdmin();
        $product = $this->makeOrder('Under Review');

        $this->post('/order-admin/under-review-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to under review order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number]);
        $this->assertSame(0, Logs::count());
    }
}
