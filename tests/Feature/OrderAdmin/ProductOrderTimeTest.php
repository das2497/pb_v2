<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\OrderTime;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * 2026-10-11: products can now be restricted to several specific order
 * times at once (e.g. Morning + Evening but not Beverage), not just one
 * single value or the blanket 'All'. Stored as a comma-separated string
 * ("Morning,Evening") - these cover the matching logic (scopeForPeriod /
 * incompatibleItemNames), the admin form's array input, and the
 * order-time delete guard, all of which had to move off an exact-match
 * comparison to a "does the list contain this value" one.
 */
class ProductOrderTimeTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    public function test_for_period_matches_a_product_restricted_to_several_times(): void
    {
        $product = Products::factory()->create(['order_time' => 'Morning,Evening']);

        $this->assertTrue(Products::forPeriod('Morning')->where('id', $product->id)->exists());
        $this->assertTrue(Products::forPeriod('Evening')->where('id', $product->id)->exists());
    }

    public function test_for_period_excludes_a_time_not_in_the_products_list(): void
    {
        OrderTime::create(['name' => 'Beverage']);
        $product = Products::factory()->create(['order_time' => 'Morning,Evening']);

        $this->assertFalse(Products::forPeriod('Beverage')->where('id', $product->id)->exists());
    }

    public function test_for_period_still_matches_the_all_sentinel(): void
    {
        $product = Products::factory()->create(['order_time' => 'All']);

        $this->assertTrue(Products::forPeriod('Morning')->where('id', $product->id)->exists());
        $this->assertTrue(Products::forPeriod('Evening')->where('id', $product->id)->exists());
    }

    public function test_incompatible_item_names_ignores_a_period_the_product_does_cover(): void
    {
        $product = Products::factory()->create(['order_time' => 'Morning,Evening', 'name_english' => 'Multi Time Item']);
        $order = Orders::create(['unique_id' => 'ORD-MULTI', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);
        Cart::create(['item_number' => $product->item_number, 'qty' => 1, 'price' => 10, 'shop_bc_number' => 'BC-1', 'order_number' => $order->unique_id]);

        $this->assertFalse(Products::incompatibleItemNames($order->unique_id, 'Evening')->contains('Multi Time Item'));
    }

    public function test_incompatible_item_names_flags_a_period_the_product_does_not_cover(): void
    {
        OrderTime::create(['name' => 'Beverage']);
        $product = Products::factory()->create(['order_time' => 'Morning,Evening', 'name_english' => 'Multi Time Item']);
        $order = Orders::create(['unique_id' => 'ORD-MULTI2', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Beverage', 'status' => 'Pending']);
        Cart::create(['item_number' => $product->item_number, 'qty' => 1, 'price' => 10, 'shop_bc_number' => 'BC-1', 'order_number' => $order->unique_id]);

        $this->assertTrue(Products::incompatibleItemNames($order->unique_id, 'Beverage')->contains('Multi Time Item'));
    }

    public function test_store_product_with_several_order_times_joins_them_with_a_comma(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-products', [
            'file' => UploadedFile::fake()->image('item.jpg'),
            'item_number' => 'ITM-MULTI',
            'item_name_e' => 'Multi Time Item',
            'item_name_s' => 'අයිතමය',
            'category' => 'CAKE ITEMS',
            'visibility' => 'All',
            'order_time' => ['Morning', 'Evening'],
            'pb_unit_price' => '100',
            'pb_mrp' => '110',
            'pb_direct_sale_price' => '90',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['item_number' => 'ITM-MULTI', 'order_time' => 'Morning,Evening']);
    }

    public function test_store_product_with_all_selected_ignores_other_selections(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-products', [
            'file' => UploadedFile::fake()->image('item.jpg'),
            'item_number' => 'ITM-ALL',
            'item_name_e' => 'All Time Item',
            'item_name_s' => 'අයිතමය',
            'category' => 'CAKE ITEMS',
            'visibility' => 'All',
            'order_time' => ['Morning', 'All'],
            'pb_unit_price' => '100',
            'pb_mrp' => '110',
            'pb_direct_sale_price' => '90',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['item_number' => 'ITM-ALL', 'order_time' => 'All']);
    }

    public function test_deleting_an_order_time_still_used_by_a_multi_time_product_is_blocked(): void
    {
        OrderTime::create(['name' => 'Beverage']);
        $beverage = OrderTime::where('name', 'Beverage')->firstOrFail();
        Products::factory()->create(['order_time' => 'Morning,Beverage']);
        $this->actingAsAdmin();

        $this->post("/order-admin/delete-order-time/{$beverage->id}")
            ->assertSessionHas('error', 'Some products still use this order time');

        $this->assertDatabaseHas('order_times', ['id' => $beverage->id]);
    }
}
