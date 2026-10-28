<?php

namespace Tests\Feature;

use App\Models\Orders;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Confirms that moving inline $request->validate([...]) calls into
 * shared Http\Requests classes (called manually as
 * $request->validate((new XRequest())->rules()), not auto-injected —
 * several of these actions run other checks, like an access gate,
 * before validating today, and auto-injection would validate before
 * the method body runs at all, reordering which error a bad request
 * sees first) didn't change the validation failure behavior itself.
 */
class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_qty_still_fails_validation_on_an_item_edit_endpoint(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'o_admin']));
        Shops::factory()->create(['branch_code' => 'BC-1']);
        Orders::create(['unique_id' => 'ORD-1', 'shop' => 'BC-1', 'total_price' => 100, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->post('/order-admin/pending-orders-update', [
            'shop' => 'BC-1', 'item_number' => 'ITM-1', 'order_number' => 'ORD-1',
        ])->assertSessionHasErrors('qty');
    }
}
