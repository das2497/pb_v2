<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Rep;
use App\Models\RepAssignShop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Dashboard extends Controller
{
    public function index()
    {

        $pending_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Pending')
            ->count('orders.id');

        $processing_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Processing')
            ->count('orders.id');

        $complete_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Complete')
            ->count('orders.id');

        $under_review_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Under Review')
            ->count('orders.id');

        // -------------------------------------------------------------------------------------------------

        // Get today's date
        $today = Carbon::today();
        $date = $today->format('Y-m-d');

        // Calculate today's total revenue
        $today_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->whereDate('orders.created_at', $date)
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('reps.email', '=', Auth::user()->email)
            ->sum('orders.total_price');

        // Get the start and end of the current week
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $endOfThisWeek = Carbon::now()->endOfWeek();

        // Calculate this week's total revenue
        $thisWeek_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->whereBetween('orders.created_at', [$startOfThisWeek, $endOfThisWeek])
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('reps.email', '=', Auth::user()->email)
            ->sum('orders.total_price');

        // Get the start and end of the current month
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth = Carbon::now()->endOfMonth();

        // Calculate this month's total revenue
        $lastMonth_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->whereBetween('orders.created_at', [$startOfThisMonth, $endOfThisMonth])
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('reps.email', '=', Auth::user()->email)
            ->sum('total_price');

        // --------------------------------------------------------------------------------------------------

        $latest_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('orders.*', 'shops.name as shop_name', 'orders.created_at as order_date')
            ->orderBy('orders.created_at')
            ->limit(10)
            ->get();

        $top_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('orders.*', 'shops.name as shop_name', 'orders.created_at as order_date')
            ->orderByDesc('orders.total_price')
            ->limit(10)
            ->get();

        // --------------------------------------------------------------------------------------------------


        $last12monthsRevenue = Orders::join('rep_assign_shops', 'orders.shop', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->select(
                DB::raw('DATE_FORMAT(orders.created_at, "%Y-%m") as month'),
                DB::raw('SUM(orders.total_price) as amount')
            )
            ->where('orders.created_at', '>=', Carbon::now()->subMonths(12))
            ->whereNot('orders.status', 'Pending')
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();



        // --------------------------------------------------------------------------------------------------

        // Calculate the date 30 days ago
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Fetch revenue data for the past 30 days
        $last30DaysRevenue = DB::table('orders')
            ->join('rep_assign_shops', 'orders.shop', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->select(DB::raw('DATE(orders.created_at) as date'), DB::raw('SUM(orders.total_price) as amount'))
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // --------------------------------------------------------------------------------------------------

        $revenues = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('shops.name as shop', DB::raw('SUM(orders.total_price) as revenue'))
            ->groupBy('shops.name')
            ->get();

        return view('rep.dashboard', [
            'pending_orders_count' => $pending_orders_count,
            'processing_orders_count' => $processing_orders_count,
            'complete_orders_count' => $complete_orders_count,
            'under_review_orders_count' => $under_review_orders_count,
            'latest_orders' => $latest_orders,
            'top_orders' => $top_orders,
            'last12monthsRevenue' => $last12monthsRevenue,
            'last30DaysRevenue' => $last30DaysRevenue,
            'today_total_revenue' => $today_total_revenue,
            'thisWeek_total_revenue' => $thisWeek_total_revenue,
            'startOfThisWeek' => $startOfThisWeek,
            'endOfThisWeek' => $endOfThisWeek,
            'lastMonth_total_revenue' => $lastMonth_total_revenue,
            'startOfThisMonth' => $startOfThisMonth,
            'endOfThisMonth' => $endOfThisMonth,
            'revenues' => $revenues
        ]);
    }
}
