<?php

namespace Tests\Feature\Rep;

use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for store() on Rep's PendingOrdersAddItems,
 * ProcessingOrdersAddItems and UnderReviewOrdersAddItems, written
 * before wiring them onto CartService. Same shape as OrderAdmin's
 * equivalents, but every one of these three is gated by
 * rep.access=='off' (OrderAdmin's add-item controllers have no gate).
 */
class AddItemTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRep(string $access = 'on'): Rep
    {
        $rep = Rep::factory()->create(['access' => $access]);
        $this->actingAs(User::factory()->create(['email' => $rep->email, 'role' => 'rep']));
        return $rep;
    }

    private function makeOrder(string $status): Products
    {
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00', 'order_time' => 'All']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => $status]);

        return $product;
    }

    public function test_pending_add_item_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrder('Pending');

        $this->post('/rep/pending-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('error', 'You do not have access to create orders.');

        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
    }

    public function test_pending_add_item_succeeds_and_logs(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrder('Pending');

        $this->post('/rep/pending-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Add To pending order']);
    }

    public function test_processing_add_item_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrder('Processing');

        $this->post('/rep/processing-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('error', 'You do not have access to create orders.');
    }

    public function test_processing_add_item_succeeds_and_logs_with_pending_wording_bug(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrder('Processing');

        $this->post('/rep/processing-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to pending order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Add To processing order']);
    }

    public function test_under_review_add_item_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrder('Under Review');

        $this->post('/rep/under-review-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('error', 'You do not have access to create orders.');
    }

    public function test_under_review_add_item_succeeds_without_logging(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrder('Under Review');

        $this->post('/rep/under-review-orders-add-item', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 3,
        ])->assertSessionHas('success', 'Product added to under review order successfully!');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertSame(0, Logs::count());
    }
}
