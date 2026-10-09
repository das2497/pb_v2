<?php

namespace Tests\Feature\Shop;

use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for store() on Shop's PendingOrdersAddItems
 * and DefaultOrdersAddItems, written before wiring them onto
 * CartService. Pending logs the mutation; Default doesn't (no Logs
 * import at all) — same divergence pattern seen in every other role's
 * add-item controllers.
 */
class AddItemTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsShop(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'shop']));
    }

    public function test_pending_add_item_creates_cart_row_bumps_total_and_logs(): void
    {
        $this->actingAsShop();
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00', 'order_time' => 'All']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->post('/shop/pending-orders-add-items-process', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 3]);
        $this->assertDatabaseHas('logs', ['type' => 'Add To pending order']);
    }

    public function test_default_add_item_bumps_total_without_logging(): void
    {
        $this->actingAsShop();
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00', 'order_time' => 'All']);
        Orders::create([
            'unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100,
            'time_period' => 'Default', 'status' => 'Default', 'default_period' => 'Morning',
        ]);

        $this->post('/shop/default-orders-add-items-process', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1', 'item_number' => $product->item_number]);
        $this->assertSame(0, Logs::count());
    }
}
