<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    /**
     * Instant shell - no DB queries. The page renders immediately and the
     * two endpoints below fill it in asynchronously (see rep/dashboard.blade.php).
     */
    public function index()
    {
        return view('rep.dashboard');
    }

    /**
     * Fast, above-the-fold numbers: order status counts and revenue sums.
     * Kept separate from getDashboardData() so the stat cards can paint
     * before the heavier chart/leaderboard queries finish.
     */
    public function firstLoading()
    {
        $repEmail = Auth::user()->email;

        $statusCounts = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', $repEmail)
            ->selectRaw("
                SUM(CASE WHEN orders.status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN orders.status = 'Processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN orders.status = 'Complete' THEN 1 ELSE 0 END) as complete,
                SUM(CASE WHEN orders.status = 'Under Review' THEN 1 ELSE 0 END) as under_review
            ")
            ->first();

        $today = Carbon::today()->format('Y-m-d');
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $endOfThisWeek = Carbon::now()->endOfWeek();
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth = Carbon::now()->endOfMonth();

        $revenues = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', $repEmail)
            ->selectRaw("
                SUM(CASE WHEN DATE(orders.created_at) = ? AND orders.status IN ('Processing', 'Complete') THEN orders.total_price ELSE 0 END) as today_total_revenue,
                SUM(CASE WHEN orders.created_at BETWEEN ? AND ? AND orders.status IN ('Processing', 'Complete') THEN orders.total_price ELSE 0 END) as thisWeek_total_revenue,
                SUM(CASE WHEN orders.created_at BETWEEN ? AND ? AND orders.status IN ('Processing', 'Complete') THEN orders.total_price ELSE 0 END) as thisMonth_total_revenue
            ", [
                $today,
                $startOfThisWeek->format('Y-m-d H:i:s'),
                $endOfThisWeek->format('Y-m-d H:i:s'),
                $startOfThisMonth->format('Y-m-d H:i:s'),
                $endOfThisMonth->format('Y-m-d H:i:s'),
            ])
            ->first();

        return response()->json([
            'stats' => [
                'pending_orders_count' => (int) ($statusCounts->pending ?? 0),
                'processing_orders_count' => (int) ($statusCounts->processing ?? 0),
                'complete_orders_count' => (int) ($statusCounts->complete ?? 0),
                'under_review_orders_count' => (int) ($statusCounts->under_review ?? 0),
                'today_total_revenue' => (float) ($revenues->today_total_revenue ?? 0),
                'thisWeek_total_revenue' => (float) ($revenues->thisWeek_total_revenue ?? 0),
                'lastMonth_total_revenue' => (float) ($revenues->thisMonth_total_revenue ?? 0),
                'startOfThisWeek' => $startOfThisWeek->format('Y-m-d'),
                'endOfThisWeek' => $endOfThisWeek->format('Y-m-d'),
                'startOfThisMonth' => $startOfThisMonth->format('Y-m-d'),
                'endOfThisMonth' => $endOfThisMonth->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Heavier chart/leaderboard data, cached per rep for a few minutes
     * since none of it needs to be real-time.
     */
    public function getDashboardData()
    {
        $repEmail = Auth::user()->email;

        $latest_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', $repEmail)
            ->select('orders.*', 'shops.name as shop_name', 'orders.created_at as order_date')
            ->orderByDesc('orders.created_at')
            ->limit(10)
            ->get();

        $top_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', $repEmail)
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->select('orders.*', 'shops.name as shop_name', 'orders.created_at as order_date')
            ->orderByDesc('orders.total_price')
            ->limit(10)
            ->get();

        $cacheKey = fn (string $suffix) => "rep-dashboard:{$repEmail}:{$suffix}";
        $cacheTtl = now()->addMinutes(10);

        $last12monthsRevenue = Cache::remember($cacheKey('last-12-months-revenue'), $cacheTtl, function () use ($repEmail) {
            return DB::table('orders')
                ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->select(
                    DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(orders.total_price) as amount')
                )
                ->where('orders.created_at', '>=', Carbon::now()->subMonths(12))
                ->whereNot('orders.status', 'Pending')
                ->where('reps.email', '=', $repEmail)
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get();
        });

        $last30DaysRevenue = Cache::remember($cacheKey('last-30-days-revenue'), $cacheTtl, function () use ($repEmail) {
            return DB::table('orders')
                ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(orders.total_price) as amount'))
                ->whereBetween('orders.created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()])
                ->where('reps.email', '=', $repEmail)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        $shopRevenues = Cache::remember($cacheKey('shop-revenues'), $cacheTtl, function () use ($repEmail) {
            return DB::table('orders')
                ->join('shops', 'orders.shop', '=', 'shops.branch_code')
                ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                ->where('reps.email', '=', $repEmail)
                ->select('shops.name as shop', DB::raw('SUM(orders.total_price) as revenue'))
                ->groupBy('shops.name')
                ->get();
        });

        return response()->json([
            'stats' => [
                'latest_orders' => $latest_orders,
                'top_orders' => $top_orders,
                'last12monthsRevenue' => $last12monthsRevenue->values(),
                'last30DaysRevenue' => $last30DaysRevenue->values(),
                'shopRevenues' => $shopRevenues->values(),
            ],
        ]);
    }
}
