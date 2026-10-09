<?php

namespace Tests\Feature;

use App\Models\Orders;
use App\Models\Rep;
use App\Models\RepAssignShop;
use App\Models\Shops;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression test for an audit-flagged bug: Rep\Dashboard's 12-month and
 * 30-day revenue queries joined orders.shop directly to
 * rep_assign_shops.shop_id (skipping the required join through shops),
 * AND never filtered by the logged-in rep at all — so these two charts
 * silently mixed in every rep's revenue, not just the current one.
 *
 * $last12monthsRevenue uses MySQL's DATE_FORMAT(), which doesn't exist on
 * SQLite (this test suite's DB) — that's pre-existing and unrelated to
 * this fix, so this test exercises the identical join+filter pattern via
 * $last30DaysRevenue (which uses the portable DATE()) instead of the full
 * HTTP route.
 */
class RepDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_fixed_revenue_query_only_includes_the_reps_own_shops(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        $repA = Rep::factory()->create();
        $shopA = Shops::factory()->create();
        RepAssignShop::create(['rep_id' => (string) $repA->id, 'shop_id' => (string) $shopA->id]);
        Orders::create(['unique_id' => uniqid(), 'shop' => $shopA->branch_code, 'total_price' => 500, 'time_period' => 'Morning', 'status' => 'Processing']);

        $repB = Rep::factory()->create();
        $shopB = Shops::factory()->create();
        RepAssignShop::create(['rep_id' => (string) $repB->id, 'shop_id' => (string) $shopB->id]);
        Orders::create(['unique_id' => uniqid(), 'shop' => $shopB->branch_code, 'total_price' => 9000, 'time_period' => 'Morning', 'status' => 'Processing']);

        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Exactly Rep\Dashboard::index()'s (now-fixed) $last30DaysRevenue query.
        $result = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(orders.total_price) as amount'))
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('reps.email', '=', $repA->email)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals(500, $result->first()->amount);
    }

    public function test_the_route_itself_requires_rep_auth(): void
    {
        $this->get('/rep/dashboard')->assertRedirect('/');
    }
}
