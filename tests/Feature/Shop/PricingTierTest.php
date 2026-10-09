<?php

namespace Tests\Feature\Shop;

use App\Models\Cart;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks in the current price-tier selection rule (price_range on the shop
 * picks unit_price / mrp / direct_sale_price on the product) before Phase 2
 * extracts it into a single PricingService. This exact branching is
 * currently copy-pasted 15+ times across OrderAdmin/Rep/Shop controllers.
 */
class PricingTierTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider priceTiers */
    public function test_add_to_cart_uses_the_price_matching_the_shops_tier(string $priceRange, string $productColumn): void
    {
        $shop = Shops::factory()->create(['price_range' => $priceRange]);
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        $product = Products::factory()->create([
            'unit_price' => '100.00',
            'mrp' => '120.00',
            'direct_sale_price' => '90.00',
        ]);

        $this->actingAs($user)->post('/shop/add-to-cart', [
            'item_number' => $product->item_number,
            'qty' => 2,
            'branch_code' => $shop->branch_code,
            'shop' => $shop->branch_code,
        ])->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'item_number' => $product->item_number,
            'shop_bc_number' => $shop->branch_code,
            'price' => $product->{$productColumn},
        ]);
    }

    public static function priceTiers(): array
    {
        return [
            'unit price tier' => ['Unit Price', 'unit_price'],
            'MRP tier' => ['PB MRP', 'mrp'],
            'direct sale tier' => ['PB Direct Sale Price', 'direct_sale_price'],
        ];
    }

    public function test_cannot_add_an_item_thats_already_in_the_cart_as_a_default_order_item(): void
    {
        $shop = Shops::factory()->create();
        $user = User::factory()->create(['role' => 'shop', 'email' => $shop->email]);
        $product = Products::factory()->create();

        // The current duplicate-guard only fires for items copied in from a
        // Default (standing order) template — it does NOT guard a second,
        // unrelated add of the same item_number. That's the real behavior
        // today (see App\Http\Controllers\Shop\CreateOrders::add_to_cart),
        // not an assumption, and is worth revisiting in the Phase 2 CartService.
        Cart::factory()->create([
            'item_number' => $product->item_number,
            'shop_bc_number' => $shop->branch_code,
            'order_number' => null,
            'default_name' => 'Default',
        ]);

        $this->actingAs($user)->post('/shop/add-to-cart', [
            'item_number' => $product->item_number,
            'qty' => 1,
            'branch_code' => $shop->branch_code,
            'shop' => $shop->branch_code,
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('carts', 1);
    }
}
