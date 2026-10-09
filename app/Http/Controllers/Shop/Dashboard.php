<?php

namespace App\Http\Controllers\Shop;

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
        $pending_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('shops.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Pending')
            ->count('orders.id');

        $processing_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('shops.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Processing')
            ->count('orders.id');

        $complete_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('shops.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Complete')
            ->count('orders.id');

        $under_review_orders_count = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
            ->where('shops.email', '=', Auth::user()->email)
            ->where('orders.status', '=', 'Under Review')
            ->count('orders.id');

        $latest_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->where('shops.email', '=', Auth::user()->email)
            ->select('orders.*', 'orders.created_at as order_date')
            ->orderBy('orders.created_at')
            ->limit(10)
            ->get();

        $top_orders = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code') // Join orders with shops
            ->where('shops.email', Auth::user()->email) // Filter by the authenticated user's email
            ->where('orders.created_at', '>=', Carbon::now()->subDays(30)) // Filter last 30 days
            ->select('orders.*', 'orders.created_at as order_date') // Select orders and order date
            ->orderByDesc('orders.total_price') // Order by total price (descending)
            ->limit(10) // Limit to top 10 orders
            ->get();

        // ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

        // Get today's date
        $today = Carbon::today();
        $date = $today->format('Y-m-d');

        // Calculate today's total revenue
        $today_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->whereDate('orders.created_at', $date)
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('shops.email', '=', Auth::user()->email)
            ->sum('total_price');

        // Get the start and end of the current week
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $endOfThisWeek = Carbon::now()->endOfWeek();

        // Calculate this week's total revenue
        $thisWeek_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->whereBetween('orders.created_at', [$startOfThisWeek, $endOfThisWeek])
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('shops.email', '=', Auth::user()->email)
            ->sum('total_price');

        // Get the start and end of the current month
        $startOfThisMonth = Carbon::now()->startOfMonth();
        $endOfThisMonth = Carbon::now()->endOfMonth();

        // Calculate this month's total revenue
        $lastMonth_total_revenue = DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->whereBetween('orders.created_at', [$startOfThisMonth, $endOfThisMonth])
            ->where(function ($query) {
                $query->where('orders.status', 'Processing')
                    ->orWhere('orders.status', 'Complete');
            })
            ->where('shops.email', '=', Auth::user()->email)
            ->sum('total_price');

        // ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------

        $shop = DB::table('shops')
            ->where('email', Auth::user()->email)
            ->first();

        $last12montsrevenue = DB::table('orders')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as amount')
            )
            ->where('shop', $shop->branch_code) // Use the shop's name to filter orders
            ->where('created_at', '>=', Carbon::now()->subMonths(12)) // Last 12 months
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // dd($last12montsrevenue);

        // ============================================================================================================================

        // Calculate the date 30 days ago
        $last30DaysRevenue = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_price) as amount')
            )
            ->where('shop', $shop->branch_code) // Use the shop's name to filter orders
            ->where('created_at', '>=', Carbon::now()->subDays(30)) // Last 30 days
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // ============================================================================================================================

        $bestSellingCategories = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number') // Join carts with products
            ->join('product_categories', 'products.category', '=', 'product_categories.category') // Join products with product_categories
            ->where('carts.shop_bc_number', $shop->branch_code) // Use the shop's branch code to filter carts
            ->select(
                'product_categories.category', // Select the category
                DB::raw('SUM(carts.qty) as total_quantity_sold'), // Sum the quantity sold
                DB::raw('SUM(carts.qty * carts.price) as total_revenue') // Calculate total revenue
            )
            ->groupBy('product_categories.category') // Group by category
            ->orderByDesc('total_quantity_sold') // Order by total quantity sold (descending)
            ->get();

        //============================================================================================================================

        $topSellingProducts = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number') // Join carts with products
            ->where('carts.shop_bc_number', $shop->branch_code) // Use the shop's branch code to filter carts
            ->select(
                'products.item_number', // Product item number
                'products.name_english', // Product name in English
                'products.name_sinhala', // Product name in Sinhala
                DB::raw('SUM(carts.qty) as total_quantity_sold'), // Total quantity sold
                DB::raw('SUM(carts.qty * carts.price) as total_revenue') // Total revenue
            )
            ->groupBy('products.item_number', 'products.name_english', 'products.name_sinhala') // Group by product
            ->orderByDesc('total_quantity_sold') // Order by total quantity sold (descending)
            ->limit(10) // Limit to top 10 products
            ->get();

        return view('shop.dashboard', [
            'pending_orders_count' => $pending_orders_count,
            'processing_orders_count' => $processing_orders_count,
            'complete_orders_count' => $complete_orders_count,
            'under_review_orders_count' => $under_review_orders_count,
            'latest_orders' => $latest_orders,
            'top_orders' => $top_orders,
            'today_total_revenue' => $today_total_revenue,
            'thisWeek_total_revenue' => $thisWeek_total_revenue,
            'startOfThisWeek' => $startOfThisWeek,
            'endOfThisWeek' => $endOfThisWeek,
            'lastMonth_total_revenue' => $lastMonth_total_revenue,
            'startOfThisMonth' => $startOfThisMonth,
            'endOfThisMonth' => $endOfThisMonth,
            'last12montsrevenue' => $last12montsrevenue,
            'last30DaysRevenue' => $last30DaysRevenue,
            'bestSellingCategories' => $bestSellingCategories,
            'topSellingProducts' => $topSellingProducts,
        ]);
    }
}
