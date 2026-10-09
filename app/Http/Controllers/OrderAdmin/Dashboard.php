<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    public function index()
    {
        return view('order-admin.dashboard');
    }

    public function firstLoading()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get today's date
        $today = Carbon::today();
        $date = $today->format('Y-m-d');

        // Get the start and end of the current week
        $startOfThisWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfThisWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Get the start and end of the current month
        $startOfThisMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfThisMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        $orderCounts = DB::table('orders')
            ->select('status', DB::raw('count(id) as count'))
            ->whereIn('status', ['Pending', 'Processing', 'Complete', 'Under Review'])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pending_orders_count = $orderCounts->get('Pending')->count ?? 0;
        $processing_orders_count = $orderCounts->get('Processing')->count ?? 0;
        $complete_orders_count = $orderCounts->get('Complete')->count ?? 0;
        $under_review_orders_count = $orderCounts->get('Under Review')->count ?? 0;

        //-------------------------------------------------------------------------------------------------

        $revenues = DB::table('orders')
            ->select(
                DB::raw("SUM(CASE WHEN DATE(created_at) = '$date' THEN total_price ELSE 0 END) as today_total_revenue"),
                DB::raw("SUM(CASE WHEN created_at BETWEEN '$startOfThisWeek' AND '$endOfThisWeek' THEN total_price ELSE 0 END) as thisWeek_total_revenue"),
                DB::raw("SUM(CASE WHEN created_at BETWEEN '$startOfThisMonth' AND '$endOfThisMonth' THEN total_price ELSE 0 END) as thisMonth_total_revenue")
            )
            ->whereIn('status', ['Processing', 'Complete'])
            ->first();

        $today_total_revenue = $revenues->today_total_revenue ?? 0;
        $thisWeek_total_revenue = $revenues->thisWeek_total_revenue ?? 0;
        $thisMonth_total_revenue = $revenues->thisMonth_total_revenue ?? 0; // Renamed from lastMonth_total_revenue for accuracy

        //-------------------------------------------------------------------------------------------------

        return response()->json([
            'stats' => [
                'pending_orders_count' => $pending_orders_count,
                'processing_orders_count' => $processing_orders_count,
                'complete_orders_count' => $complete_orders_count,
                'under_review_orders_count' => $under_review_orders_count,
                'today_total_revenue' => $today_total_revenue,
                'last7Days_total_revenue' => $thisWeek_total_revenue,
                'lastMonth_total_revenue' => $thisMonth_total_revenue, // Note:
                'startOfThisMonth' => $startOfThisMonth,
                'endOfThisMonth' => $endOfThisMonth,
                'startOfThisWeek' => $startOfThisWeek,
                'endOfThisWeek' => $endOfThisWeek,
            ],
        ]);
    }

    public function getDashboardData()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Get today's date
        $today = Carbon::today();
        $date = $today->format('Y-m-d');

        // Get the start and end of the current week
        $startOfThisWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfThisWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

        // Get the start and end of the current month
        $startOfThisMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfThisMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        $orderCounts = DB::table('orders')
            ->select('status', DB::raw('count(id) as count'))
            ->whereIn('status', ['Pending', 'Processing', 'Complete', 'Under Review'])
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $pending_orders_count = $orderCounts->get('Pending')->count ?? 0;
        $processing_orders_count = $orderCounts->get('Processing')->count ?? 0;
        $complete_orders_count = $orderCounts->get('Complete')->count ?? 0;
        $under_review_orders_count = $orderCounts->get('Under Review')->count ?? 0;

        //-------------------------------------------------------------------------------------------------

        $revenues = DB::table('orders')
            ->select(
                DB::raw("SUM(CASE WHEN DATE(created_at) = '$date' THEN total_price ELSE 0 END) as today_total_revenue"),
                DB::raw("SUM(CASE WHEN created_at BETWEEN '$startOfThisWeek' AND '$endOfThisWeek' THEN total_price ELSE 0 END) as thisWeek_total_revenue"),
                DB::raw("SUM(CASE WHEN created_at BETWEEN '$startOfThisMonth' AND '$endOfThisMonth' THEN total_price ELSE 0 END) as thisMonth_total_revenue")
            )
            ->whereIn('status', ['Processing', 'Complete'])
            ->first();

        $today_total_revenue = $revenues->today_total_revenue ?? 0;
        $thisWeek_total_revenue = $revenues->thisWeek_total_revenue ?? 0;
        $thisMonth_total_revenue = $revenues->thisMonth_total_revenue ?? 0; // Renamed from lastMonth_total_revenue for accuracy

        //-------------------------------------------------------------------------------------------------

        $data = Orders::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total_price) as amount')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->whereNot('status', 'Pending')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month', 'ASC')
            ->get();

        // Ensure all 12 months are present (optional, with 0 for missing months)
        $allMonths = collect();
        for ($i = 11; $i >= 0; $i--) {
            $allMonths->push(Carbon::now()->subMonths($i)->format('Y-m'));
        }

        $last12montsrevenue = $allMonths->map(function ($month) use ($data) {
            $row = $data->firstWhere('month', $month);
            return [
                'month' => $month,
                'amount' => $row ? (float) $row->amount : 0
            ];
        });

        $topSellingShops = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->select('shops.name', DB::raw('SUM(orders.total_price) as total_sales'))
            ->groupBy('shops.name', 'shops.branch_code') // include branch_code to be safe
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        $topItems = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number')
            ->select(
                'products.name_english',
                'products.item_number',
                DB::raw('SUM(CAST(carts.qty AS DECIMAL(10,2))) as total_qty_sold')
            )
            ->groupBy('products.item_number', 'products.name_english')
            ->orderByDesc('total_qty_sold')
            ->limit(10)
            ->get();

        $revenueData = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with 0 revenue for smooth chart
        $dates = collect();
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->put($date->toDateString(), 0);
        }

        foreach ($revenueData as $row) {
            $dates->put($row->date, (float) $row->total_revenue);
        }

        //-------------------------------------------------------------------------------------------------

        $topSellingRepsAllTime = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->select(
                'reps.name',
                DB::raw('SUM(orders.total_price) as total_sales')
            )
            ->groupBy('reps.id', 'reps.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->map(function ($rep) {
                return [
                    'name' => $rep->name,
                    'total_sales' => (float) $rep->total_sales,
                    'formatted' => 'Rs ' . number_format($rep->total_sales, 2),
                ];
            });

        //-------------------------------------------------------------------------------------------------

        $topSellingRepsToday = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->whereDate('orders.created_at', $today) // cleaner than >= $date
            ->select(
                'reps.name',
                DB::raw('SUM(orders.total_price) as total_sales')
            )
            ->groupBy('reps.id', 'reps.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->map(function ($rep) {
                return [
                    'name' => $rep->name,
                    'total_sales' => (float) $rep->total_sales,
                    'formatted' => 'Rs ' . number_format($rep->total_sales, 2),
                ];
            });

        //-------------------------------------------------------------------------------------------------

        $topSellingRepsLast30Days = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'reps.name',
                DB::raw('SUM(orders.total_price) as total_sales')
            )
            ->groupBy('reps.id', 'reps.name')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get()
            ->map(function ($rep) {
                return [
                    'name' => $rep->name,
                    'total_sales' => (float) $rep->total_sales,
                    'formatted' => 'Rs ' . number_format($rep->total_sales, 2),
                ];
            });

        //-------------------------------------------------------------------------------------------------

        $latest_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->select('orders.*', 'shops.*', 'orders.created_at as order_created_at')
            ->orderByDesc('orders.created_at')
            ->limit(10)
            ->get();

        $top_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->select(
                'orders.unique_id',
                'orders.total_price',
                'orders.created_at as order_created_at',
                'orders.time_period',
                'shops.name'
            )
            ->whereNot('orders.status', 'Cancelled')
            ->orderByDesc('orders.total_price')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'pending_orders_count' => $pending_orders_count,
                'processing_orders_count' => $processing_orders_count,
                'complete_orders_count' => $complete_orders_count,
                'under_review_orders_count' => $under_review_orders_count,
                'today_total_revenue' => $today_total_revenue,
                'last7Days_total_revenue' => $thisWeek_total_revenue,
                'lastMonth_total_revenue' => $thisMonth_total_revenue, // Note: Renamed internally, but kept view key as is
                'startOfThisMonth' => $startOfThisMonth,
                'endOfThisMonth' => $endOfThisMonth,
                'startOfThisWeek' => $startOfThisWeek,
                'endOfThisWeek' => $endOfThisWeek,
                'last12montsrevenue' => $last12montsrevenue->values(),
                'topSellingShops' => [
                    'topSellingShops_labels' => $topSellingShops->pluck('name')->toArray(),
                    'topSellingShops_data' => $topSellingShops->pluck('total_sales')->toArray(),
                ],
                'topSellingItems' => [
                    'topItems_labels' => $topItems->pluck('name_english')->toArray(),
                    'topItems_data' => $topItems->pluck('total_qty_sold')->map(fn($v) => (float) $v)->toArray(),
                ],
                'revenueData' => [
                    'labels' => $dates->keys()->toArray(),
                    'data' => $dates->values()->toArray(),
                ],
                'topSellingRepsAllTime' => [
                    'chartData' => [
                        'labels' => $topSellingRepsAllTime->pluck('name')->toArray(),
                        'data' => $topSellingRepsAllTime->pluck('total_sales')->toArray(),
                    ],
                    'tableData' => $topSellingRepsAllTime->toArray(),
                ],
                'topSellingRepsToday' => [
                    'chartData' => [
                        'labels' => $topSellingRepsToday->pluck('name')->toArray(),
                        'data' => $topSellingRepsToday->pluck('total_sales')->toArray(),
                    ],
                    'tableData' => $topSellingRepsToday->toArray(),
                ],
                'topSellingRepsLast30Days' => [
                    'chartData' => [
                        'labels' => $topSellingRepsLast30Days->pluck('name')->toArray(),
                        'data' => $topSellingRepsLast30Days->pluck('total_sales')->toArray(),
                    ],
                    'tableData' => $topSellingRepsLast30Days->toArray(),
                ],
                'latest_orders' => $latest_orders,
                'top_orders' => $top_orders,

            ],
        ]);
    }
}


