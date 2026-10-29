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
     * Every orders query below used to re-run this same
     * orders⋈shops⋈rep_assign_shops⋈reps join itself just to find out
     * which shops belong to this rep - at real data volume that join alone
     * measured ~370-420ms, paid again on every single widget. Resolved
     * once per request (and cached briefly, since a rep's shop list
     * changes rarely) and reused as a plain branch_code list, so every
     * orders query below can filter with a cheap, indexed
     * `whereIn('orders.shop', ...)` instead of repeating the join.
     *
     * @return \Illuminate\Support\Collection<int, object{branch_code: string, name: string}>
     */
    private function shopsForRep(string $repEmail)
    {
        return Cache::remember(
            "rep-dashboard:{$repEmail}:shops",
            now()->addMinutes(10),
            fn () => DB::table('rep_assign_shops')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->join('shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
                ->where('reps.email', '=', $repEmail)
                ->select('shops.branch_code', 'shops.name')
                ->get()
        );
    }

    /**
     * Fast, above-the-fold numbers: order status counts and revenue sums.
     * Kept separate from getDashboardData() so the stat cards can paint
     * before the heavier chart/leaderboard queries finish. Cached briefly -
     * at real data volume a busy rep's full order history is genuinely
     * several thousand rows to aggregate (~350-420ms uncached, confirmed
     * via EXPLAIN ANALYZE to already be an efficient indexed range scan,
     * not a missing-index problem), so a short cache absorbs repeat loads
     * within a browsing session without the numbers going stale for long.
     */
    public function firstLoading()
    {
        $repEmail = Auth::user()->email;
        $shopCodes = $this->shopsForRep($repEmail)->pluck('branch_code')->all();

        $stats = Cache::remember("rep-dashboard:{$repEmail}:first-loading", now()->addSeconds(30), function () use ($shopCodes) {
            $today = Carbon::today()->format('Y-m-d');
            $startOfThisWeek = Carbon::now()->startOfWeek();
            $endOfThisWeek = Carbon::now()->endOfWeek();
            $startOfThisMonth = Carbon::now()->startOfMonth();
            $endOfThisMonth = Carbon::now()->endOfMonth();

            // Status counts and revenue sums used to be two separate passes
            // over the same rep-scoped order set - merged into one query
            // since both read the same rows.
            $row = DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->selectRaw("
                    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing,
                    SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as complete,
                    SUM(CASE WHEN status = 'Under Review' THEN 1 ELSE 0 END) as under_review,
                    SUM(CASE WHEN DATE(created_at) = ? AND status IN ('Processing', 'Complete') THEN total_price ELSE 0 END) as today_total_revenue,
                    SUM(CASE WHEN created_at BETWEEN ? AND ? AND status IN ('Processing', 'Complete') THEN total_price ELSE 0 END) as thisWeek_total_revenue,
                    SUM(CASE WHEN created_at BETWEEN ? AND ? AND status IN ('Processing', 'Complete') THEN total_price ELSE 0 END) as thisMonth_total_revenue
                ", [
                    $today,
                    $startOfThisWeek->format('Y-m-d H:i:s'),
                    $endOfThisWeek->format('Y-m-d H:i:s'),
                    $startOfThisMonth->format('Y-m-d H:i:s'),
                    $endOfThisMonth->format('Y-m-d H:i:s'),
                ])
                ->first();

            return [
                'pending_orders_count' => (int) ($row->pending ?? 0),
                'processing_orders_count' => (int) ($row->processing ?? 0),
                'complete_orders_count' => (int) ($row->complete ?? 0),
                'under_review_orders_count' => (int) ($row->under_review ?? 0),
                'today_total_revenue' => (float) ($row->today_total_revenue ?? 0),
                'thisWeek_total_revenue' => (float) ($row->thisWeek_total_revenue ?? 0),
                'lastMonth_total_revenue' => (float) ($row->thisMonth_total_revenue ?? 0),
                'startOfThisWeek' => $startOfThisWeek->format('Y-m-d'),
                'endOfThisWeek' => $endOfThisWeek->format('Y-m-d'),
                'startOfThisMonth' => $startOfThisMonth->format('Y-m-d'),
                'endOfThisMonth' => $endOfThisMonth->format('Y-m-d'),
            ];
        });

        return response()->json(['stats' => $stats]);
    }

    /**
     * Heavier chart/leaderboard data, cached per rep for a few minutes
     * since none of it needs to be real-time.
     */
    public function getDashboardData()
    {
        $repEmail = Auth::user()->email;
        $shops = $this->shopsForRep($repEmail);
        $shopCodes = $shops->pluck('branch_code')->all();
        $shopNames = $shops->pluck('name', 'branch_code');

        $cacheKey = fn (string $suffix) => "rep-dashboard:{$repEmail}:{$suffix}";
        $cacheTtl = now()->addMinutes(10);
        $shortTtl = now()->addSeconds(30);

        $attachShopName = function ($orders) use ($shopNames) {
            return $orders->each(function ($order) use ($shopNames) {
                $order->shop_name = $shopNames[$order->shop] ?? null;
            });
        };

        $latest_orders = Cache::remember($cacheKey('latest-orders'), $shortTtl, function () use ($shopCodes, $attachShopName) {
            return $attachShopName(DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->select('orders.*', 'orders.created_at as order_date')
                ->orderByDesc('orders.created_at')
                ->limit(10)
                ->get());
        });

        $top_orders = Cache::remember($cacheKey('top-orders'), $shortTtl, function () use ($shopCodes, $attachShopName) {
            return $attachShopName(DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
                ->select('orders.*', 'orders.created_at as order_date')
                ->orderByDesc('orders.total_price')
                ->limit(10)
                ->get());
        });

        $last12monthsRevenue = Cache::remember($cacheKey('last-12-months-revenue'), $cacheTtl, function () use ($shopCodes) {
            return DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->select(
                    DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m") as month'),
                    DB::raw('SUM(orders.total_price) as amount')
                )
                ->where('orders.created_at', '>=', Carbon::now()->subMonths(12))
                ->whereNot('orders.status', 'Pending')
                ->groupBy('month')
                ->orderBy('month', 'ASC')
                ->get();
        });

        $last30DaysRevenue = Cache::remember($cacheKey('last-30-days-revenue'), $cacheTtl, function () use ($shopCodes) {
            return DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(orders.total_price) as amount'))
                ->whereBetween('orders.created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()])
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        $shopRevenues = Cache::remember($cacheKey('shop-revenues'), $cacheTtl, function () use ($shopCodes, $shopNames) {
            $byShop = DB::table('orders')
                ->whereIn('orders.shop', $shopCodes)
                ->select('orders.shop as shop_code', DB::raw('SUM(orders.total_price) as revenue'))
                ->groupBy('orders.shop')
                ->get();

            // Re-grouped by shop name in PHP (cheap - at most a few dozen
            // rows) rather than in SQL, to match the original query's
            // `groupBy('shops.name')` exactly - including two shops that
            // happen to share a display name - without joining `shops`
            // into the heavy orders aggregate to get the name.
            return $byShop
                ->groupBy(fn ($row) => $shopNames[$row->shop_code] ?? $row->shop_code)
                ->map(fn ($rows, $name) => ['shop' => $name, 'revenue' => $rows->sum('revenue')])
                ->values();
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
