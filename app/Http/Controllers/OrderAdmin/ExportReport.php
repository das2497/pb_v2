<?php

namespace App\Http\Controllers\OrderAdmin;

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
        $fromDate = $request->input('start_date') ?? $currentDate;
        $toDate = $request->input('end_date') ?? $currentDate;

        // === HANDLE MULTIPLE ROUTES ===
        $selectedRoutes = $request->input('routes', []);
        if (!is_array($selectedRoutes)) {
            $selectedRoutes = $selectedRoutes ? [$selectedRoutes] : [];
        }

        $state = $request->input('state') ?? 'Pending';

        // === HANDLE MULTIPLE CATEGORIES ===
        $selectedCategories = $request->input('categories', []);
        if (!is_array($selectedCategories)) {
            $selectedCategories = $selectedCategories ? [$selectedCategories] : [];
        }

        // === HANDLE MULTIPLE TIME PERIODS ===
        $selectedTimePeriods = $request->input('time_period', []);
        if (!is_array($selectedTimePeriods)) {
            $selectedTimePeriods = $selectedTimePeriods ? [$selectedTimePeriods] : [];
        }
        if (empty($selectedTimePeriods)) {
            $selectedTimePeriods = ['Morning'];
        }
        // Shops only have a morning_route / evening_route column each (no
        // per-time-period route), so route matching stays the same as
        // before: an exclusively-Morning selection matches morning_route,
        // an exclusively-Evening selection matches evening_route, and any
        // other selection (mixed, or a period like Beverage/Chips that has
        // no route of its own) matches either - same fallback the single
        // -select version used for every non-Morning/Evening value.
        $isMorningOnly = $selectedTimePeriods === ['Morning'];
        $isEveningOnly = $selectedTimePeriods === ['Evening'];

        // ================= SHOPS – FILTER BY SELECTED ROUTES =================
        $shopsQuery = DB::table('shops')->select('shops.*', DB::raw('shops.name as shop_name'));

        if (!empty($selectedRoutes)) {
            $shopsQuery->where(function ($query) use ($selectedRoutes, $isMorningOnly, $isEveningOnly) {
                if ($isMorningOnly) {
                    $query->whereIn('morning_route', $selectedRoutes);
                } elseif ($isEveningOnly) {
                    $query->whereIn('evening_route', $selectedRoutes);
                } else {
                    $query->whereIn('morning_route', $selectedRoutes)
                        ->orWhereIn('evening_route', $selectedRoutes);
                }
            });
        }

        $allShops = $shopsQuery->get();

        // ================= CATEGORIES & ROUTES =================
        $categories = DB::table('product_categories')
            ->distinct()
            ->select('category')
            ->get();

        $routes = DB::table('routes')
            ->distinct()
            ->select('name')
            ->get();

        $orderTimes = DB::table('order_times')->orderBy('id')->pluck('name');

        // ================= PRODUCTS =================
        $productsQuery = DB::table('products');
        if (!empty($selectedCategories)) {
            $productsQuery->whereIn('category', $selectedCategories);
        } else {
            $productsQuery->whereRaw('1 = 0'); // no products if no category
        }

        $products = $productsQuery->orderBy('item_number', 'asc')->get();

        $shopCodes = $allShops->pluck('branch_code')->map(fn($c) => trim($c))->toArray();

        // ================= ORDERS =================
        $ordersQuery = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->whereIn('orders.time_period', $selectedTimePeriods)
            ->where('orders.status', $state)
            ->whereBetween('orders.created_at', [
                $fromDate . ' 00:00:00',
                $toDate . ' 23:59:59'
            ])
            ->whereIn('orders.shop', $shopCodes);

        if (!empty($selectedCategories)) {
            $ordersQuery->join('products', 'carts.item_number', '=', 'products.item_number')
                ->whereIn('products.category', $selectedCategories);
        }

        $orders = $ordersQuery->select(
            'orders.shop',
            'carts.item_number',
            'carts.qty',
            'carts.remarke'
        )->get();

        // ================= AGGREGATION =================
        $aggregatedOrders = [];
        $columnTotals = []; // shop column totals
        $shopTotals = [];

        foreach ($orders as $order) {
            $shop = trim($order->shop);
            $item = $order->item_number;
            $qty = (double) $order->qty;

            $aggregatedOrders[$shop][$item]['qty'] = ($aggregatedOrders[$shop][$item]['qty'] ?? 0) + $qty;
            $aggregatedOrders[$shop][$item]['remark'] = $aggregatedOrders[$shop][$item]['remark'] ?? ($order->remarke ?? '');

            $shopTotals[$shop] = ($shopTotals[$shop] ?? 0) + $qty;
            $columnTotals[$shop] = ($columnTotals[$shop] ?? 0) + $qty;
        }

        // ================= FILTER SHOPS & PRODUCTS WITH ORDERS =================
        $filteredShops = $allShops->filter(function ($shop) use ($shopTotals) {
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

        // ================= GROUP SHOPS BY ROUTE =================
        $shopsGroupedByRoute = $filteredShops->groupBy(function ($shop) use ($isMorningOnly) {
            return $isMorningOnly ? $shop->morning_route : $shop->evening_route;
        })->sortKeys();

        return view('order-admin.export-report', [
            'shops' => $filteredShops,
            'shopsGroupedByRoute' => $shopsGroupedByRoute,
            'products' => $filteredProducts,
            'aggregatedOrders' => $aggregatedOrders,
            'columnTotals' => $columnTotals,
            'route' => $selectedRoutes,
            'state' => $state,
            'routes' => $routes,
            'categories' => $categories,
            'orderTimes' => $orderTimes,
            'selectedCategories' => $selectedCategories,
            'currentDate' => $currentDate,
            'selectedTimePeriods' => $selectedTimePeriods,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ]);
    }

}
