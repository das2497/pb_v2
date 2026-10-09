<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportReport extends Controller
{

    public function index(Request $request)
    {
        $currentDate = Carbon::now()->toDateString();
        $fromDate    = $request->input('start_date') ?? $currentDate;
        $toDate      = $request->input('end_date') ?? $currentDate;
        $route       = $request->input('route') ?? 'ROUTE 1';
        $state       = $request->input('state') ?? 'Pending';

        // THIS IS THE KEY CHANGE: accept array of categories
        $selectedCategories = $request->input('categories', []); // array
        if (!is_array($selectedCategories)) {
            $selectedCategories = $selectedCategories ? [$selectedCategories] : [];
        }

        $timePeriod  = $request->input('time_period') ?? 'Morning';

        // ================= SHOPS =================
        $shops = DB::table('shops')
            ->where(function ($query) use ($route) {
                $query->where('morning_route', $route)
                    ->orWhere('evening_route', $route);
            })
            ->select('shops.*', DB::raw('shops.name as shop_name'))
            ->get();

        // ================= CATEGORIES (for dropdown) =================
        $categories = DB::table('product_categories')
            ->distinct()
            ->select('category')
            ->get();

        // ================= ROUTES =================
        $routes = DB::table('routes')
            ->distinct()
            ->select('name')
            ->get();

        // ================= PRODUCTS – NOW FILTER BY MULTIPLE CATEGORIES =================
        $productsQuery = DB::table('products');

        if (!empty($selectedCategories)) {
            $productsQuery->whereIn('category', $selectedCategories);
        } else {
            // Optional: if no category selected, maybe show nothing or all?
            // Most people prefer to show nothing if nothing selected
            $productsQuery->whereRaw('1 = 0'); // forces empty result
            // OR remove this else block to show all categories when none selected
        }

        $products = $productsQuery
            ->orderBy('item_number', 'asc')
            ->get();

        $shopCodes = $shops->pluck('branch_code')->map(fn($c) => trim($c))->toArray();

        // ================= ORDERS =================
        $orders = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', $timePeriod)
            ->where('orders.status', $state)
            ->whereBetween('orders.created_at', [
                $fromDate . ' 00:00:00',
                $toDate . ' 23:59:59'
            ])
            ->whereIn('orders.shop', $shopCodes);

        // IMPORTANT: Also filter carts by selected categories (more accurate)
        if (!empty($selectedCategories)) {
            $orders->join('products', 'carts.item_number', '=', 'products.item_number')
                ->whereIn('products.category', $selectedCategories);
        }

        $orders = $orders->select(
            'orders.shop',
            'carts.item_number',
            'carts.qty',
            'carts.remarke'
        )
            ->get();

        // ================= AGGREGATION (remains the same) =================
        $aggregatedOrders = [];
        $shopTotals = [];

        foreach ($orders as $order) {
            $shop = trim($order->shop);
            $item = $order->item_number;
            $qty  = (double) $order->qty;

            $aggregatedOrders[$shop][$item]['qty']     = ($aggregatedOrders[$shop][$item]['qty'] ?? 0) + $qty;
            $aggregatedOrders[$shop][$item]['remark']  = $aggregatedOrders[$shop][$item]['remark'] ?? ($order->remarke ?? '');
            $shopTotals[$shop]                         = ($shopTotals[$shop] ?? 0) + $qty;
        }

        // ================= HIDE EMPTY SHOPS & PRODUCTS (unchanged) =================
        $filteredShops = $shops->filter(function ($shop) use ($shopTotals) {
            $code = trim($shop->branch_code);
            return ($shopTotals[$code] ?? 0) > 0;
        })->values();

        $filteredProducts = $products->filter(function ($product) use ($aggregatedOrders, $filteredShops) {
            foreach ($filteredShops as $shop) {
                $code = trim($shop->branch_code);
                if (($aggregatedOrders[$code][$product->item_number]['qty'] ?? 0) > 0) {
                    return true;
                }
            }
            return false;
        })->values();        

        return view('order-admin.export-report', [
            'shops'            => $filteredShops,
            'products'         => $filteredProducts,
            'aggregatedOrders' => $aggregatedOrders,
            'route'            => $route,
            'state'            => $state,
            'routes'           => $routes,
            'categories'       => $categories,
            'selectedCategories' => $selectedCategories, // pass back so Blade can pre-select
            'currentDate'      => $currentDate,
            'timePeriod'       => $timePeriod,
            'fromDate'         => $fromDate,
            'toDate'           => $toDate,
        ]);
    }


    // public function index(Request $request)
    // {
    //     $fromDate = $request->input('start_date') ?? '2024-09-26';
    //     $toDate = $request->input('end_date') ?? '2024-09-26';
    //     $currentDate =  Carbon::now()->toDateString();
    //     $route = $request->input('route') ?? 'ROUTE 1';
    //     $category = $request->input('category') ?? 'CAKE ITEMS';
    //     $timePeriod = $request->input('time_period') ?? 'Morning';

    //     // ================= SHOPS =================
    //     $shops = DB::table('shops')
    //         ->where(function ($query) use ($route) {
    //             $query->where('morning_route', $route)
    //                 ->orWhere('evening_route', $route);
    //         })
    //         ->select('shops.*', DB::raw('shops.name as shop_name'))
    //         ->get();

    //     // ================= CATEGORIES =================
    //     $categories = DB::table('product_categories')
    //         ->distinct()
    //         ->select('category')
    //         ->get();

    //     // ================= ROUTES =================
    //     $routes = DB::table('routes')
    //         ->distinct()
    //         ->select('name')
    //         ->get();


    //     // ================= PRODUCTS =================
    //     $products = DB::table('products')
    //         ->where('category', $category)
    //         ->orderBy('item_number', 'asc')
    //         ->get();

    //     $shopCodes = $shops->pluck('branch_code')->map(fn($c) => trim($c))->toArray();

    //     // ================= ORDERS =================
    //     $orders = DB::table('orders')
    //         ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
    //         ->where('orders.time_period', $timePeriod)
    //         ->where('orders.status', 'Complete')
    //         ->whereBetween('orders.created_at', [
    //             $fromDate . ' 00:00:00',
    //             $toDate . ' 23:59:59'
    //         ])
    //         ->whereIn('orders.shop', $shopCodes)
    //         ->select(
    //             'orders.shop',
    //             'carts.item_number',
    //             'carts.qty',
    //             'carts.remarke'
    //         )
    //         ->get();

    //     // ================= AGGREGATION =================
    //     $aggregatedOrders = [];
    //     $shopTotals = [];

    //     foreach ($orders as $order) {

    //         $shop = trim($order->shop);
    //         $item = $order->item_number;
    //         $qty  = (int) $order->qty;

    //         $aggregatedOrders[$shop][$item]['qty'] = ($aggregatedOrders[$shop][$item]['qty'] ?? 0) + $qty;

    //         $aggregatedOrders[$shop][$item]['remark'] = $aggregatedOrders[$shop][$item]['remark'] ?? ($order->remarke ?? '');

    //         $shopTotals[$shop] = ($shopTotals[$shop] ?? 0) + $qty;
    //     }

    //     // ================= HIDE EMPTY SHOPS =================
    //     $filteredShops = $shops->filter(function ($shop) use ($shopTotals) {
    //         $code = trim($shop->branch_code);
    //         return ($shopTotals[$code] ?? 0) > 0;
    //     })->values();

    //     // ================= HIDE EMPTY PRODUCTS =================
    //     $filteredProducts = $products->filter(function ($product) use ($aggregatedOrders, $filteredShops) {

    //         foreach ($filteredShops as $shop) {
    //             $code = trim($shop->branch_code);

    //             if (($aggregatedOrders[$code][$product->item_number]['qty'] ?? 0) > 0) {
    //                 return true;
    //             }
    //         }

    //         return false;
    //     })->values();

    //     return view('order-admin.export-report', [
    //         'shops'            => $filteredShops,
    //         'products'         => $filteredProducts,
    //         'aggregatedOrders' => $aggregatedOrders,
    //         'route'            => $route,
    //         'routes'           => $routes,
    //         'categories'         => $categories,
    //         'currentDate'      => $currentDate
    //     ]);
    // }
}

