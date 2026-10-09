<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Smoke tests for the OrderAdmin CRUD endpoints whose inline
 * $request->validate([...]) calls were just moved into
 * App\Http\Requests classes (StoreShopRequest, UpdateShopRequest,
 * StoreRepRequest, RouteRequest, ProductCategoryRequest,
 * StoreOrderTimeRequest, RepAssignShopRequest, StoreProductRequest,
 * UpdateProductRequest). None of these had any prior test coverage.
 * Posting the exact same valid payload the controller expected before
 * and asserting success would fail immediately if a rule's field name
 * was mistyped while moving it — that's what this guards against, not
 * new validation behavior.
 */
class AdminCrudFormRequestTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    public function test_store_shop_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-shop', [
            'name' => 'Test Shop',
            'name_sinhala' => 'ටෙස්ට්',
            'branch_code' => 'BC-99',
            'email' => 'shop99@example.com',
            'contact' => '0771234567',
            'price_range' => 'Unit Price',
            'morning_route' => 'Route A',
            'evening_route' => 'Route B',
            'type' => 'Normal',
            'order_time' => 'All',
            'password' => 'secret123',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('shops', ['branch_code' => 'BC-99']);
    }

    public function test_update_shop_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create();

        $this->put("/shops/{$shop->id}", [
            'branch_code' => $shop->branch_code,
            'email' => 'updated@example.com',
            'price_range' => 'Unit Price',
            'morning_route' => 'Route A',
            'evening_route' => 'Route B',
            'order_time' => 'All',
            'type' => 'Normal',
            'name' => 'Ignored (not validated today)',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'email' => 'updated@example.com']);
    }

    public function test_store_rep_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-rep', [
            'name' => 'Test Rep',
            'email' => 'rep99@example.com',
            'contact' => '0771234567',
            'type' => 'Normal',
            'password' => 'secret123',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('reps', ['email' => 'rep99@example.com']);
    }

    public function test_store_route_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-route', [
            'index' => 1,
            'name' => 'Test Route',
            'type' => 'Normal',
            'time' => 'Morning',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('routes', ['name' => 'Test Route']);
    }

    public function test_update_route_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $route = Routes::create(['index' => 1, 'name' => 'Old', 'type' => 'Normal', 'time' => 'Morning']);

        $this->put("/order-admin/update-route/{$route->id}", [
            'index' => 2,
            'name' => 'New',
            'type' => 'Special',
            'time' => 'Evening',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('routes', ['id' => $route->id, 'name' => 'New']);
    }

    public function test_store_product_category_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-category', [
            'main_category' => 'Cakes',
            'category' => 'Icing Display Cakes',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('product_categories', ['category' => 'Icing Display Cakes']);
    }

    public function test_update_product_category_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $category = Product_category::create(['main_category' => 'Old', 'category' => 'Old Cat']);

        $this->put("/order-admin/update-category/{$category->id}", [
            'main_category' => 'New',
            'category' => 'New Cat',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('product_categories', ['id' => $category->id, 'category' => 'New Cat']);
    }

    public function test_store_order_time_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-order-time', [
            'name' => 'Afternoon',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('order_times', ['name' => 'Afternoon']);
    }

    public function test_store_order_time_rejects_a_reserved_name(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-order-time', [
            'name' => 'All',
        ])->assertSessionHasErrors('name');
    }

    public function test_rep_assign_shop_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $rep = Rep::factory()->create();
        $shop = Shops::factory()->create();

        $this->post('/order-admin/rep-assign', [
            'rep_id' => $rep->id,
            'shop_id' => $shop->id,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('rep_assign_shops', ['rep_id' => $rep->id, 'shop_id' => $shop->id]);
    }

    public function test_store_product_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();

        $this->post('/order-admin/add-products', [
            'file' => UploadedFile::fake()->image('item.jpg'),
            'item_number' => 'ITM-NEW',
            'item_name_e' => 'New Item',
            'item_name_s' => 'අලුත් අයිතමය',
            'category' => 'CAKE ITEMS',
            'visibility' => 'All',
            'order_time' => 'All',
            'pb_unit_price' => '100',
            'pb_mrp' => '110',
            'pb_direct_sale_price' => '90',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['item_number' => 'ITM-NEW']);
    }

    public function test_update_product_succeeds_with_valid_data(): void
    {
        $this->actingAsAdmin();
        $product = Products::factory()->create();

        $this->put("/products/{$product->id}", [
            'item_number' => $product->item_number,
            'name_english' => 'Updated Name',
            'name_sinhala' => 'යටත්',
            'category' => 'CAKE ITEMS',
            'visibility' => 'All',
            'order_time' => 'All',
            'unit_price' => '50',
            'mrp' => '55',
            'direct_sale_price' => '45',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('products', ['item_number' => $product->item_number, 'name_english' => 'Updated Name']);
    }
}
