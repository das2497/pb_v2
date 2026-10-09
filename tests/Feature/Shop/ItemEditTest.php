<?php

namespace Tests\Feature\Shop;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for Shop\PendingOrders::update()/delete() —
 * the only Shop listing controller with item-edit methods at all;
 * Shop\ProcessingOrders and Shop\UnderReviewOrders are view-only.
 */
class ItemEditTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsShop(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'shop']));
    }

    private function makeOrderWithCartItem(): Products
    {
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);
        Cart::factory()->create(['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 2]);

        return $product;
    }

    public function test_update_adjusts_total_price_cart_and_logs(): void
    {
        $this->actingAsShop();
        $product = $this->makeOrderWithCartItem();

        $this->post('/shop/pending-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5, 'remarke' => 'r',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 5]);
        $this->assertDatabaseHas('logs', ['type' => 'Update item pending Order']);
    }

    public function test_delete_adjusts_total_price_removes_cart_row_and_logs(): void
    {
        $this->actingAsShop();
        $product = $this->makeOrderWithCartItem();

        $this->post('/shop/pending-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
        $this->assertDatabaseHas('logs', ['type' => 'Delete item pending Order']);
    }
}
