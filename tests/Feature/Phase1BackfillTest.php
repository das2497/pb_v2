<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\RepAssignShop;
use App\Models\Role;
use App\Models\Routes;
use App\Models\Shops;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies `phase1:backfill` correctly resolves the new FK columns from
 * the existing string soft-relationships, and correctly leaves unresolved
 * rows alone (rather than guessing) — including the ambiguous case for
 * logs.user_id, where the same name matches more than one user.
 */
class Phase1BackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_backfills_user_role_id_and_reports_unmatched_roles(): void
    {
        $matched = User::factory()->create(['role' => 'rep']);
        $unmatched = User::factory()->create(['role' => 'not_a_real_role']);

        $this->artisan('phase1:backfill');

        $this->assertSame(Role::where('slug', 'rep')->value('id'), $matched->refresh()->role_id);
        $this->assertNull($unmatched->refresh()->role_id);
    }

    public function test_it_backfills_order_shop_id_and_reports_unmatched_branch_codes(): void
    {
        $shop = Shops::factory()->create(['branch_code' => 'BC-100']);
        $matched = Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-100', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);
        $unmatched = Orders::create(['unique_id' => uniqid(), 'shop' => 'BC-999', 'total_price' => 10, 'time_period' => 'Morning', 'status' => 'Pending']);

        $this->artisan('phase1:backfill');

        $this->assertSame($shop->id, $matched->refresh()->shop_id);
        $this->assertNull($unmatched->refresh()->shop_id);
    }

    public function test_it_backfills_shop_route_ids_independently_for_morning_and_evening(): void
    {
        $morningRoute = Routes::factory()->create(['name' => 'Known Morning Route']);
        $shop = Shops::factory()->create([
            'morning_route' => 'Known Morning Route',
            'evening_route' => 'Unknown Evening Route',
        ]);

        $this->artisan('phase1:backfill');

        $shop->refresh();
        $this->assertSame($morningRoute->id, $shop->morning_route_id);
        $this->assertNull($shop->evening_route_id);
    }

    public function test_it_backfills_log_user_id_only_when_the_name_match_is_unique(): void
    {
        $uniqueUser = User::factory()->create(['name' => 'Only One Of Me']);
        User::factory()->create(['name' => 'Duplicate Name']);
        User::factory()->create(['name' => 'Duplicate Name']);

        $resolvable = Logs::create(['type' => 'Test', 'message' => 'x', 'user' => 'Only One Of Me']);
        $ambiguous = Logs::create(['type' => 'Test', 'message' => 'x', 'user' => 'Duplicate Name']);
        $noMatch = Logs::create(['type' => 'Test', 'message' => 'x', 'user' => 'Nobody Like This']);

        $this->artisan('phase1:backfill');

        $this->assertSame($uniqueUser->id, $resolvable->refresh()->user_id);
        $this->assertNull($ambiguous->refresh()->user_id);
        $this->assertNull($noMatch->refresh()->user_id);
    }

    public function test_it_backfills_rep_assign_shop_int_ids_only_for_valid_numeric_references(): void
    {
        $rep = \App\Models\Rep::factory()->create();
        $shop = Shops::factory()->create();

        $valid = RepAssignShop::create(['rep_id' => (string) $rep->id, 'shop_id' => (string) $shop->id]);
        $invalid = RepAssignShop::create(['rep_id' => 'not-a-number', 'shop_id' => '999999']);

        $this->artisan('phase1:backfill');

        $valid->refresh();
        $this->assertSame($rep->id, $valid->rep_id_int);
        $this->assertSame($shop->id, $valid->shop_id_int);

        $invalid->refresh();
        $this->assertNull($invalid->rep_id_int);
        $this->assertNull($invalid->shop_id_int);
    }

    public function test_it_copies_remarke_into_remark(): void
    {
        $cart = Cart::factory()->create(['remarke' => 'original note']);

        $this->artisan('phase1:backfill');

        $this->assertSame('original note', $cart->refresh()->remark);
    }
}
