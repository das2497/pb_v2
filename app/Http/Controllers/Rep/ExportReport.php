<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportReport extends Controller
{
    /**
     * Instant shell - only the cheap filter-dropdown lookups (routes,
     * categories) run here. The heavy shop/product/order aggregation runs
     * in data() below, fetched asynchronously by the page's JS so the
     * report loads instantly instead of blocking on it up front.
     */
    public function index(Request $request)
    {
        $currentDate = Carbon::now()->toDateString();

        return view('rep.export-report', [
            'routes' => DB::table('routes')->distinct()->select('name')->get(),
            'categories' => DB::table('product_categories')->distinct()->select('category')->get(),
            'orderTimes' => DB::table('order_times')->orderBy('id')->pluck('name'),
            'currentDate' => $currentDate,
            'fromDate' => $request->input('start_date') ?? $currentDate,
            'toDate' => $request->input('end_date') ?? $currentDate,
            'timePeriod' => $request->input('time_period') ?? 'Morning',
            'state' => $request->input('state') ?? 'Pending',
            'selectedRoutes' => $this->normalizeArray($request->input('routes', [])),
            'selectedCategories' => $this->normalizeArray($request->input('categories', [])),
        ]);
    }

    /**
     * The actual report table, aggregated from a single orders+carts query
     * (not one query per shop/product cell) and rendered as an HTML
     * fragment for the page's fetch() call to inject.
     */
    public function data(Request $request)
    {
        $currentDate = Carbon::now()->toDateString();
        $fromDate = $request->input('start_date') ?? $currentDate;
        $toDate = $request->input('end_date') ?? $currentDate;
        $state = $request->input('state') ?? 'Pending';
        $timePeriod = $request->input('time_period') ?? 'Morning';

        $selectedRoutes = $this->normalizeArray($request->input('routes', []));
        $selectedCategories = $this->normalizeArray($request->input('categories', []));

        $shopsQuery = DB::table('shops')->select('shops.*', DB::raw('shops.name as shop_name'));

        if (!empty($selectedRoutes)) {
            $shopsQuery->where(function ($query) use ($selectedRoutes, $timePeriod) {
                if ($timePeriod === 'Morning') {
                    $query->whereIn('morning_route', $selectedRoutes);
                } elseif ($timePeriod === 'Evening') {
                    $query->whereIn('evening_route', $selectedRoutes);
                } else {
                    $query->whereIn('morning_route', $selectedRoutes)
                        ->orWhereIn('evening_route', $selectedRoutes);
                }
            });
        }

        $allShops = $shopsQuery->get();

        $productsQuery = DB::table('products');
        if (!empty($selectedCategories)) {
            $productsQuery->whereIn('category', $selectedCategories);
        } else {
            // No category picked yet - show nothing rather than every
            // product in the system.
            $productsQuery->whereRaw('1 = 0');
        }
        $products = $productsQuery->orderBy('item_number', 'asc')->get();

        $shopCodes = $allShops->pluck('branch_code')->map(fn ($c) => trim($c))->toArray();

        $ordersQuery = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', $timePeriod)
            ->where('orders.status', $state)
            ->whereBetween('orders.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
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

        $aggregatedOrders = [];
        $columnTotals = [];
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

        $filteredShops = $allShops->filter(function ($shop) use ($shopTotals) {
            return ($shopTotals[trim($shop->branch_code)] ?? 0) > 0;
        })->values();

        $filteredProducts = $products->filter(function ($product) use ($aggregatedOrders, $filteredShops) {
            foreach ($filteredShops as $shop) {
                if (($aggregatedOrders[trim($shop->branch_code)][$product->item_number]['qty'] ?? 0) > 0) {
                    return true;
                }
            }
            return false;
        })->values();

        $shopsGroupedByRoute = $filteredShops->groupBy(function ($shop) use ($timePeriod) {
            return $timePeriod === 'Morning' ? $shop->morning_route : $shop->evening_route;
        })->sortKeys();

        return view('rep.partials.export-report-table', [
            'shopsGroupedByRoute' => $shopsGroupedByRoute,
            'products' => $filteredProducts,
            'aggregatedOrders' => $aggregatedOrders,
            'columnTotals' => $columnTotals,
            'hasCategories' => !empty($selectedCategories),
        ]);
    }

    private function normalizeArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return $value ? [$value] : [];
    }
}
