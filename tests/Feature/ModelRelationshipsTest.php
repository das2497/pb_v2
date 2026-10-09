<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\RepAssignShop;
use App\Models\Role;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 2: before this, none of the 11 original models had a single
 * Eloquent relationship — every lookup was a manual DB::table() join or a
 * sequential Model::where()->first() chain. These tests prove the new
 * relationships resolve the same data those manual lookups did.
 */
class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_its_shop_via_shop_id(): void
    {
        $shop = Shops::factory()->create();
        $order = Orders::create(['unique_id' => uniqid(), 'shop' => $shop->branch_code, 'shop_id' => $shop->id, 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->assertTrue($order->shopRecord->is($shop));
    }

    public function test_shop_has_many_orders(): void
    {
        $shop = Shops::factory()->create();
        Orders::create(['unique_id' => uniqid(), 'shop' => $shop->branch_code, 'shop_id' => $shop->id, 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);
        Orders::create(['unique_id' => uniqid(), 'shop' => $shop->branch_code, 'shop_id' => $shop->id, 'total_price' => 200, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->assertCount(2, $shop->orders);
    }

    public function test_order_has_many_cart_items_via_order_number(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 50, 'time_period' => 'Morning', 'status' => 'Pending']);
        Cart::factory()->create(['order_number' => 'ORD-1']);
        Cart::factory()->create(['order_number' => 'ORD-1']);
        Cart::factory()->create(['order_number' => null]);

        $this->assertCount(2, $order->cartItems);
    }

    public function test_cart_belongs_to_its_order_and_product(): void
    {
        $order = Orders::create(['unique_id' => 'ORD-2', 'shop' => 'BC-1', 'total_price' => 50, 'time_period' => 'Morning', 'status' => 'Pending']);
        $product = Products::factory()->create();
        $cart = Cart::factory()->create(['order_number' => 'ORD-2', 'item_number' => $product->item_number]);

        $this->assertTrue($cart->order->is($order));
        $this->assertTrue($cart->product->is($product));
    }

    public function test_shop_belongs_to_morning_and_evening_routes(): void
    {
        $morning = Routes::factory()->create(['time' => 'Morning']);
        $evening = Routes::factory()->create(['time' => 'Evening']);
        $shop = Shops::factory()->create(['morning_route_id' => $morning->id, 'evening_route_id' => $evening->id]);

        $this->assertTrue($shop->morningRoute->is($morning));
        $this->assertTrue($shop->eveningRoute->is($evening));
        $this->assertTrue($morning->morningShops->contains($shop));
        $this->assertTrue($evening->eveningShops->contains($shop));
    }

    public function test_rep_and_shop_are_linked_through_the_typed_pivot_columns(): void
    {
        $rep = Rep::factory()->create();
        $shop = Shops::factory()->create();
        RepAssignShop::create(['rep_id' => (string) $rep->id, 'rep_id_int' => $rep->id, 'shop_id' => (string) $shop->id, 'shop_id_int' => $shop->id]);

        $this->assertTrue($rep->shops->contains($shop));
        $this->assertTrue($shop->reps->contains($rep));
    }

    public function test_rep_and_shop_user_accounts_are_matched_by_email(): void
    {
        $rep = Rep::factory()->create();
        $repUser = User::factory()->create(['email' => $rep->email, 'role' => 'rep']);
        $shop = Shops::factory()->create();
        $shopUser = User::factory()->create(['email' => $shop->email, 'role' => 'shop']);

        $this->assertTrue($rep->user->is($repUser));
        $this->assertTrue($shop->user->is($shopUser));
    }

    public function test_log_belongs_to_its_user_via_user_id(): void
    {
        $user = User::factory()->create();
        $log = Logs::create(['type' => 'Test', 'message' => 'x', 'user' => $user->name, 'user_id' => $user->id]);

        $this->assertTrue($log->userRecord->is($user));
    }

    public function test_user_belongs_to_its_role_record(): void
    {
        $role = Role::where('slug', 'rep')->first();
        $user = User::factory()->create(['role' => 'rep', 'role_id' => $role->id]);

        $this->assertTrue($user->roleRecord->is($role));
        $this->assertTrue($role->users->contains($user));
    }

    public function test_product_belongs_to_its_category_record(): void
    {
        $category = Product_category::factory()->create(['category' => 'CAKE ITEMS']);
        $product = Products::factory()->create(['category' => 'CAKE ITEMS']);

        $this->assertTrue($product->categoryRecord->is($category));
        $this->assertTrue($category->products->contains($product));
    }

    /** @dataProvider priceTiers */
    public function test_product_price_for_matches_pricing_service(string $priceRange, string $column): void
    {
        $shop = Shops::factory()->create(['price_range' => $priceRange]);
        $product = Products::factory()->create(['unit_price' => '10.00', 'mrp' => '12.00', 'direct_sale_price' => '9.00']);

        $this->assertSame((float) $product->{$column}, $product->priceFor($shop));
        $this->assertSame($product->priceFor($shop), PricingService::priceFor($product, $shop));
    }

    public static function priceTiers(): array
    {
        return [
            ['Unit Price', 'unit_price'],
            ['PB MRP', 'mrp'],
            ['PB Direct Sale Price', 'direct_sale_price'],
        ];
    }
}
