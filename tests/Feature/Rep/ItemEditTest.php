<?php

namespace Tests\Feature\Rep;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for the item quantity-update/delete methods on
 * Rep's PendingOrders, ProcessingOrders and UnderReviewOrders, written
 * before wiring them onto CartService. Same core logic as OrderAdmin's
 * equivalents, but every method here (except UnderReviewOrders::delete)
 * is gated by rep.access=='off' — and the gate's error message differs:
 * Pending's delete has its own wording, everything else reuses the
 * generic "You do not have access to create orders." text.
 */
class ItemEditTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsRep(string $access = 'on'): Rep
    {
        $rep = Rep::factory()->create(['access' => $access]);
        $this->actingAs(User::factory()->create(['email' => $rep->email, 'role' => 'rep']));
        return $rep;
    }

    private function makeOrderWithCartItem(string $status): Products
    {
        Shops::factory()->create(['branch_code' => 'BC-1', 'price_range' => 'Unit Price']);
        $product = Products::factory()->create(['unit_price' => '10.00']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => $status]);
        Cart::factory()->create(['order_number' => 'ORD-1', 'item_number' => $product->item_number, 'qty' => 2]);

        return $product;
    }

    // -- Pending ----------------------------------------------------------

    public function test_pending_update_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Pending');

        $this->post('/rep/pending-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5,
        ])->assertSessionHas('error', 'You do not have access to create orders.');

        $this->assertSame(100.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
    }

    public function test_pending_update_succeeds_and_logs(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrderWithCartItem('Pending');

        $this->post('/rep/pending-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5, 'remarke' => 'r',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Update item Pending Order']);
    }

    public function test_pending_delete_blocked_when_access_off_with_its_own_message(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Pending');

        $this->post('/rep/pending-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('error', 'You do not have access to delete pending order items now.');

        $this->assertDatabaseHas('carts', ['order_number' => 'ORD-1']);
    }

    public function test_pending_delete_succeeds_and_logs(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrderWithCartItem('Pending');

        $this->post('/rep/pending-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
        $this->assertDatabaseHas('logs', ['type' => 'Delete item Pending Order']);
    }

    // -- Processing ---------------------------------------------------------

    public function test_processing_update_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Processing');

        $this->post('/rep/processing-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5,
        ])->assertSessionHas('error', 'You do not have access to create orders.');
    }

    public function test_processing_update_succeeds_and_logs(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrderWithCartItem('Processing');

        $this->post('/rep/processing-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5, 'remarke' => 'r',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Update item processing Order']);
    }

    public function test_processing_delete_blocked_when_access_off_with_generic_message(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Processing');

        $this->post('/rep/processing-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('error', 'You do not have access to create orders.');
    }

    public function test_processing_delete_succeeds_and_logs(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrderWithCartItem('Processing');

        $this->post('/rep/processing-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertDatabaseHas('logs', ['type' => 'Delete item processing Order']);
    }

    // -- Under Review -------------------------------------------------------

    public function test_under_review_update_blocked_when_access_off(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Under Review');

        $this->post('/rep/under-review-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5,
        ])->assertSessionHas('error', 'You do not have access to create orders.');
    }

    public function test_under_review_update_succeeds_without_logging(): void
    {
        $this->actingAsRep();
        $product = $this->makeOrderWithCartItem('Under Review');

        $this->post('/rep/under-review-orders-update', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 5, 'remarke' => 'r',
        ])->assertSessionHas('success', 'Quantity Updated Successfully');

        $this->assertSame(130.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertSame(0, Logs::count());
    }

    public function test_under_review_delete_succeeds_with_no_access_gate_and_no_logging(): void
    {
        $this->actingAsRep('off');
        $product = $this->makeOrderWithCartItem('Under Review');

        $this->post('/rep/under-review-orders-delete', [
            'shop' => 'BC-1', 'item_number' => $product->item_number, 'order_number' => 'ORD-1', 'qty' => 2,
        ])->assertSessionHas('success', 'Successfully deleted!');

        $this->assertSame(80.0, (float) Orders::where('unique_id', 'ORD-1')->value('total_price'));
        $this->assertSame(0, Logs::count());
    }

    public function test_under_review_delete_order_removes_order_and_its_cart_items(): void
    {
        $this->actingAsRep();
        $this->makeOrderWithCartItem('Under Review');

        $this->post('/rep/under-review-orders-delete/ORD-1')
            ->assertSessionHas('success', 'Order Deleted Successfully');

        $this->assertDatabaseMissing('orders', ['unique_id' => 'ORD-1']);
        $this->assertDatabaseMissing('carts', ['order_number' => 'ORD-1']);
    }
}
