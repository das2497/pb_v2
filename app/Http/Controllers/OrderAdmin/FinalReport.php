<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalReport extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Morning');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('morning_route', 'Morning', $state, $currentDate);

        return view('order-admin.final-report-morning', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function fullScreen(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Morning');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('morning_route', 'Morning', $state, $currentDate);

        return view('order-admin.final-report-morning-full-screen', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function index_evening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Evening');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('evening_route', 'Evening', $state, $currentDate);

        return view('order-admin.final-report-evening', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function fullScreen_evening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Evening');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('evening_route', 'Evening', $state, $currentDate);

        return view('order-admin.final-report-evening-full-screen', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    /**
     * Full (unpaginated) morning report table, for "Export to Excel" on the
     * main final-report page. Honors the same date/state filters as index()
     * so the export matches whatever the admin currently has on screen.
     */
    public function exportMorning(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Morning');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('morning_route', 'Morning', $state, $currentDate);

        return view('order-admin.partials.final-report-export-table', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
        ]);
    }

    /**
     * Full (unpaginated) evening report table, for "Export to Excel" on the
     * evening final-report page.
     */
    public function exportEvening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $routes = $this->routesByType('Evening');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildRouteOrderMap('evening_route', 'Evening', $state, $currentDate);

        return view('order-admin.partials.final-report-export-table', [
            'routes_normal' => $routes['Normal'] ?? collect(),
            'routes_special' => $routes['Special'] ?? collect(),
            'routes_pbd' => $routes['PBD'] ?? collect(),
            'products' => $products,
            'orderMap' => $orderMap,
        ]);
    }

    /**
     * Routes for the given time ('Morning' / 'Evening'), grouped by type
     * (Normal / Special / PBD) in one query instead of one query per type.
     */
    private function routesByType(string $time)
    {
        return DB::table('routes')
            ->where('time', $time)
            ->get()
            ->groupBy('type');
    }

    /**
     * Sums every matching order+cart row once, grouped by route and item,
     * instead of running one SUM() query per product x route table cell.
     * Returns route_name => item_number => total_qty.
     */
    private function buildRouteOrderMap(string $routeColumn, string $timePeriod, string $status, string $date): array
    {
        $rows = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->where('orders.time_period', $timePeriod)
            ->where('orders.status', $status)
            ->whereDate('orders.created_at', $date)
            ->select("shops.$routeColumn as route", 'carts.item_number', DB::raw('SUM(carts.qty) as total_qty'))
            ->groupBy("shops.$routeColumn", 'carts.item_number')
            ->get();

        $orderMap = [];
        foreach ($rows as $row) {
            $orderMap[$row->route][$row->item_number] = $row->total_qty;
        }

        return $orderMap;
    }
}
