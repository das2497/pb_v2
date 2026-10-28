<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    /**
     * Instant shell - no DB queries. The page renders immediately and the
     * two endpoints below fill it in asynchronously (see shop/dashboard.blade.php).
     */
    public function index()
    {
        return view('shop.dashboard');
    }

    /**
     * Fast, above-the-fold numbers: order status counts and revenue sums.
     * Kept separate from getDashboardData() so the stat cards can paint
     * before the heavier chart/leaderboard queries finish.
     */
    public function firstLoading()
    {
        $shop = DB::table('shops')->where('email', Auth::user()->email)->first();

        $statusCounts = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('shops.email', '=', Auth::user()->email)
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
            ->where('orders.shop', $shop->branch_code)
            ->selectRaw("
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
     * Heavier chart/leaderboard data, cached per shop for a few minutes
     * since none of it needs to be real-time.
     */
    public function getDashboardData()
    {
        $shop = DB::table('shops')->where('email', Auth::user()->email)->first();

        $latest_orders = DB::table('orders')
            ->where('orders.shop', $shop->branch_code)
            ->select('orders.*', 'orders.created_at as order_date')
            ->orderByDesc('orders.created_at')
            ->limit(10)
            ->get();

        $top_orders = DB::table('orders')
            ->where('orders.shop', $shop->branch_code)
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30))
            ->select('orders.*', 'orders.created_at as order_date')
            ->orderByDesc('orders.total_price')
            ->limit(10)
            ->get();

        $cacheKey = fn (string $suffix) => "shop-dashboard:{$shop->branch_code}:{$suffix}";
        $cacheTtl = now()->addMinutes(10);

        $last12montsrevenue = Cache::remember($cacheKey('last-12-months-revenue'), $cacheTtl, function () use ($shop) {
            return DB::table('orders')
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_price) as amount')
                )
                ->where('shop', $shop->branch_code)
                ->where('created_at', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
        });

        $last30DaysRevenue = Cache::remember($cacheKey('last-30-days-revenue'), $cacheTtl, function () use ($shop) {
            return DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as amount')
                )
                ->where('shop', $shop->branch_code)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        });

        $bestSellingCategories = Cache::remember($cacheKey('best-selling-categories'), $cacheTtl, function () use ($shop) {
            return DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->join('product_categories', 'products.category', '=', 'product_categories.category')
                ->where('carts.shop_bc_number', $shop->branch_code)
                ->select(
                    'product_categories.category',
                    DB::raw('SUM(carts.qty) as total_quantity_sold'),
                    DB::raw('SUM(carts.qty * carts.price) as total_revenue')
                )
                ->groupBy('product_categories.category')
                ->orderByDesc('total_quantity_sold')
                ->get();
        });

        $topSellingProducts = Cache::remember($cacheKey('top-selling-products'), $cacheTtl, function () use ($shop) {
            return DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->where('carts.shop_bc_number', $shop->branch_code)
                ->select(
                    'products.item_number',
                    'products.name_english',
                    'products.name_sinhala',
                    DB::raw('SUM(carts.qty) as total_quantity_sold'),
                    DB::raw('SUM(carts.qty * carts.price) as total_revenue')
                )
                ->groupBy('products.item_number', 'products.name_english', 'products.name_sinhala')
                ->orderByDesc('total_quantity_sold')
                ->limit(10)
                ->get();
        });

        return response()->json([
            'stats' => [
                'latest_orders' => $latest_orders,
                'top_orders' => $top_orders,
                'last12montsrevenue' => $last12montsrevenue->values(),
                'last30DaysRevenue' => $last30DaysRevenue->values(),
                'bestSellingCategories' => $bestSellingCategories->values(),
                'topSellingProducts' => $topSellingProducts->values(),
            ],
        ]);
    }
}
