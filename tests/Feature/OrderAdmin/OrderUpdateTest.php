<?php

namespace Tests\Feature\OrderAdmin;

use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Characterization tests for update_order() on Pending_orders,
 * Processing_orders and Under_review_orders. Written before any
 * refactor to lock in real, current behavior — including a genuine
 * divergence: Pending_orders' cancel checkbox only takes effect when a
 * conflicting same-day order already exists for that shop+period; with
 * no conflict, "cancel" silently falls through to a normal period
 * reassignment instead (Processing/Under_review both have a standalone
 * cancel branch that Pending lacks). Also: Under_review never touches
 * the `note` column, Processing and Pending both do.
 */
class OrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
    }

    // -- Pending_orders::update_order -----------------------------------

    public function test_pending_cancel_with_a_conflicting_order_cancels_and_sets_note(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'OTHER', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Morning', 'status' => 'Pending']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
            'order_cancel' => 'on',
            'note' => 'cancel note',
        ])->assertSessionHas('success', 'Successfully cancelled order!');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
        $this->assertSame('cancel note', $order->note);
    }

    public function test_pending_cancel_with_no_conflicting_order_does_not_cancel(): void
    {
        $this->actingAsAdmin();
        $shop = Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
            'order_cancel' => 'on',
            'note' => 'ignored cancel intent',
        ])->assertSessionHas('success', 'Updated successfully');

        $order->refresh();
        $this->assertSame('Pending', $order->status);
        $this->assertSame('Morning', $order->time_period);
        $this->assertSame('ignored cancel intent', $order->note);
    }

    public function test_pending_blocks_reassignment_when_existing_order_conflicts_and_not_cancelling(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'OTHER', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Morning', 'status' => 'Pending']);
        Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
        ])->assertSessionHas('error', 'Order already created for this time period in this shop.');
    }

    public function test_pending_blocks_reassignment_to_a_period_not_allowed_for_the_shop(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'Evening']);
        Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
        ])->assertSessionHas('error', 'This order time is not allowed for this shop.');
    }

    public function test_pending_blocks_reassignment_when_cart_has_incompatible_items(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);
        $product = Products::factory()->create(['order_time' => 'Evening']);
        Cart::factory()->create(['order_number' => 'TARGET', 'item_number' => $product->item_number]);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
        ])->assertSessionHas('error');

        $this->assertSame('Evening', $order->refresh()->time_period);
    }

    public function test_pending_successfully_reassigns_period_and_note_without_touching_status(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_number' => 'TARGET',
            'note' => 'reassigned',
        ])->assertSessionHas('success', 'Updated successfully');

        $order->refresh();
        $this->assertSame('Morning', $order->time_period);
        $this->assertSame('Pending', $order->status);
        $this->assertSame('reassigned', $order->note);
    }

    // -- Processing_orders::update_order ---------------------------------

    public function test_processing_cancel_with_a_conflicting_order_cancels_and_sets_note(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'OTHER', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Morning', 'status' => 'Processing']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Processing']);

        $this->post('/order-admin/processing-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_cancel' => 'on',
            'note' => 'cancel note',
        ])->assertSessionHas('success', 'Success!');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
        $this->assertSame('cancel note', $order->note);
    }

    public function test_processing_cancel_with_no_conflicting_order_still_cancels(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Processing']);

        $this->post('/order-admin/processing-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_cancel' => 'on',
            'note' => 'cancel note',
        ])->assertSessionHas('success', 'Order successfully cancelled');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
        $this->assertSame('cancel note', $order->note);
    }

    public function test_processing_blocks_reassignment_when_existing_order_conflicts_and_not_cancelling(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'OTHER', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Morning', 'status' => 'Processing']);
        Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Processing']);

        $this->post('/order-admin/processing-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
        ])->assertSessionHas('error', 'Order already created for this time period in this shop.');
    }

    public function test_processing_successfully_reassigns_period_status_and_note(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Processing']);

        $this->post('/order-admin/processing-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'state' => 'Processing',
            'note' => 'reassigned',
        ])->assertSessionHas('success', 'Updated successfully');

        $order->refresh();
        $this->assertSame('Morning', $order->time_period);
        $this->assertSame('Processing', $order->status);
        $this->assertSame('reassigned', $order->note);
    }

    // -- Under_review_orders::update_order -------------------------------

    public function test_under_review_cancel_with_a_conflicting_order_cancels_without_touching_note(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'OTHER', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Morning', 'status' => 'Under Review']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Under Review', 'note' => 'original note']);

        $this->post('/order-admin/under-review-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_cancel' => 'on',
            'note' => 'this note should be ignored',
        ])->assertSessionHas('success', 'Success!');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
        $this->assertSame('original note', $order->note);
    }

    public function test_under_review_cancel_with_no_conflicting_order_still_cancels_without_touching_note(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Under Review', 'note' => 'original note']);

        $this->post('/order-admin/under-review-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'order_cancel' => 'on',
        ])->assertSessionHas('success', 'Order successfully cancelled');

        $order->refresh();
        $this->assertSame('Cancelled', $order->status);
        $this->assertSame('Cancelled', $order->time_period);
        $this->assertSame('original note', $order->note);
    }

    public function test_under_review_successfully_reassigns_period_and_status_without_touching_note(): void
    {
        $this->actingAsAdmin();
        Shops::factory()->create(['branch_code' => 'BC-1', 'order_time' => 'All']);
        $order = Orders::create(['unique_id' => 'TARGET', 'shop' => 'BC-1', 'total_price' => 1, 'time_period' => 'Evening', 'status' => 'Under Review', 'note' => 'original note']);

        $this->post('/order-admin/under-review-orders-update-order', [
            'shop' => 'BC-1',
            'period' => 'Morning',
            'order_id' => 'TARGET',
            'state' => 'Under Review',
        ])->assertSessionHas('success', 'Updated successfully');

        $order->refresh();
        $this->assertSame('Morning', $order->time_period);
        $this->assertSame('Under Review', $order->status);
        $this->assertSame('original note', $order->note);
    }
}
