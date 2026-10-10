<?php

namespace Tests\Feature\Services;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService();
        $this->actingAs(User::factory()->create(['role' => 'shop']));
    }

    /** @dataProvider priceTiers */
    public function test_add_to_cart_prices_the_item_by_the_shops_tier(string $priceRange, string $column): void
    {
        $shop = Shops::factory()->create(['price_range' => $priceRange]);
        $product = Products::factory()->create(['unit_price' => '10.00', 'mrp' => '12.00', 'direct_sale_price' => '9.00']);

        $cart = $this->service->addToCart($shop, $product, 3);

        $this->assertSame((float) $product->{$column}, (float) $cart->price);
        $this->assertSame(3, (int) $cart->qty);
    }

    public static function priceTiers(): array
    {
        return [
            ['Unit Price', 'unit_price'],
            ['PB MRP', 'mrp'],
            ['PB Direct Sale Price', 'direct_sale_price'],
        ];
    }

    public function test_cannot_add_an_item_already_in_the_cart_as_a_default_order_item(): void
    {
        $shop = Shops::factory()->create();
        $product = Products::factory()->create();
        Cart::factory()->create([
            'item_number' => $product->item_number,
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
            'default_name' => 'Default',
        ]);

        $this->expectExceptionMessage('Cart already have this item');
        $this->service->addToCart($shop, $product, 1);
    }

    public function test_a_second_rapid_add_to_cart_from_the_same_actor_is_rejected(): void
    {
        $shop = Shops::factory()->create();
        $product1 = Products::factory()->create();
        $product2 = Products::factory()->create();

        $this->service->addToCart($shop, $product1, 1);

        $this->expectExceptionMessage('Add to cart button');
        $this->service->addToCart($shop, $product2, 1);
    }

    public function test_add_all_to_cart_creates_one_row_per_item_and_logs_once(): void
    {
        $shop = Shops::factory()->create(['price_range' => 'Unit Price']);
        $productA = Products::factory()->create(['unit_price' => '10.00']);
        $productB = Products::factory()->create(['unit_price' => '20.00']);

        $created = $this->service->addAllToCart($shop, [
            ['item_number' => $productA->item_number, 'qty' => 2],
            ['item_number' => $productB->item_number, 'qty' => 0], // skipped: qty 0
        ], 'Tester');

        $this->assertCount(1, $created);
        $this->assertDatabaseHas('carts', ['item_number' => $productA->item_number, 'qty' => 2]);
        $this->assertDatabaseMissing('carts', ['item_number' => $productB->item_number]);
        $this->assertDatabaseHas('logs', ['type' => 'Add To Cart']);
    }

    public function test_update_qty_updates_quantity_and_remark_and_logs(): void
    {
        $cart = Cart::factory()->create(['item_number' => 'ITM-1', 'shop_bc_number' => 'BC-1', 'order_number' => null, 'qty' => '1']);

        $this->service->updateQty('ITM-1', 'BC-1', 5, 'extra sweet', 'Tester');

        $cart->refresh();
        $this->assertSame(5, (int) $cart->qty);
        $this->assertSame('extra sweet', $cart->remarke);
        $this->assertDatabaseHas('logs', ['type' => 'Update quantity']);
    }

    public function test_delete_item_removes_the_row_and_logs(): void
    {
        Cart::factory()->create(['item_number' => 'ITM-2', 'shop_bc_number' => 'BC-1', 'order_number' => null]);

        $this->service->deleteItem('ITM-2', 'BC-1', 'Tester');

        $this->assertDatabaseMissing('carts', ['item_number' => 'ITM-2', 'shop_bc_number' => 'BC-1']);
        $this->assertDatabaseHas('logs', ['type' => 'Delete item']);
    }

    public function test_add_item_to_existing_order_creates_the_row_and_bumps_the_order_total(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-ADD', 'shop' => 'BC-1', 'total_price' => 50, 'time_period' => 'Morning', 'status' => 'Pending']);

        $cart = $this->service->addItemToExistingOrder('ORD-ADD', 'ITM-3', 'BC-1', 10.0, 2);

        $this->assertSame('ITM-3', $cart->item_number);
        $this->assertSame('ORD-ADD', $cart->order_number);
        $this->assertSame(70.0, (float) $order->refresh()->total_price);
    }

    public function test_apply_order_item_qty_change_adjusts_the_order_total_by_the_delta(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-QTY', 'shop' => 'BC-1', 'total_price' => 50, 'time_period' => 'Morning', 'status' => 'Pending']);
        $cart = Cart::factory()->create(['item_number' => 'ITM-4', 'shop_bc_number' => 'BC-1', 'order_number' => 'ORD-QTY', 'qty' => 2]);

        $this->service->applyOrderItemQtyChange('ORD-QTY', 'ITM-4', 10.0, 5, 'less sugar');

        $this->assertSame(80.0, (float) $order->refresh()->total_price);
        $this->assertSame(5, (int) $cart->refresh()->qty);
        $this->assertSame('less sugar', $cart->remarke);
    }

    public function test_remove_order_item_reverses_its_contribution_and_deletes_the_row(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-REM', 'shop' => 'BC-1', 'total_price' => 50, 'time_period' => 'Morning', 'status' => 'Pending']);
        Cart::factory()->create(['item_number' => 'ITM-5', 'shop_bc_number' => 'BC-1', 'order_number' => 'ORD-REM', 'qty' => 3]);

        $this->service->removeOrderItem('ORD-REM', 'ITM-5', 10.0, 3);

        $this->assertSame(20.0, (float) $order->refresh()->total_price);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-REM', 'item_number' => 'ITM-5']);
    }
}
