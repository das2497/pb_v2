<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopReport extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('morning_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('rep.shop-report-morning', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function fullScreen(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('morning_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('rep.shop-report-morning-full-screen', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function back_to_report()
    {
        return redirect('/rep/shop-report-morning');
    }

    public function index_evening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('evening_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('rep.shop-report-evening', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function fullScreen_evening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('evening_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('rep.shop-report-evening-full-screen', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function back_to_report_evening()
    {
        return redirect('/rep/shop-report-evening');
    }

    /**
     * Full (unpaginated) morning report table, for "Export to Excel" on the
     * main shop-report page. Honors the same date/state filters as index()
     * so the export matches whatever the rep currently has on screen.
     */
    public function exportMorning(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('morning_route');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('rep.partials.shop-report-morning-export-table', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
        ]);
    }

    /**
     * Full (unpaginated) evening report table, for "Export to Excel" on the
     * evening shop-report page. Honors the same date/state filters as
     * index_evening() so the export matches whatever the rep currently has
     * on screen.
     */
    public function exportEvening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteAndType('evening_route');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('rep.partials.shop-report-evening-export-table', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
        ]);
    }

    /**
     * Shops assigned to the logged-in rep, joined to their route for the
     * given route-time column (morning_route / evening_route), grouped by
     * route type (Normal / Special / PBD) and then by individual route
     * (route_id) - the same two-level grouping
     * OrderAdmin\ShopReport::shopsByRouteAndType() uses, so each route gets
     * its own "{route name} Total" column instead of only one combined
     * total per type. The rep-scoping (rep_assign_shops/reps join) is what
     * keeps this to only the logged-in rep's own shops - not filtered by
     * routes.time, for the same reason as the admin version: a route's
     * type/shops apply regardless of which time period it happens to be
     * tagged with.
     */
    private function shopsByRouteAndType(string $routeColumn)
    {
        return DB::table('shops')
            ->join('routes', 'routes.name', '=', "shops.$routeColumn")
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->whereIn('routes.type', ['Normal', 'Special', 'PBD'])
            ->select('shops.*', 'shops.name as shop_name', 'routes.id as route_id', 'routes.name as route_name', 'routes.type as route_type')
            ->orderBy('routes.id')
            ->get()
            ->groupBy('route_type')
            ->map(fn ($group) => $group->groupBy('route_id'));
    }

    /**
     * Fetches every matching order+cart row once and structures it as
     * shop => item_number => ['qty' => ..., 'remark' => ...] so the report
     * view can look up each cell in memory instead of querying per cell.
     */
    private function buildOrderMap(string $timePeriod, string $status, string $date): array
    {
        $orders = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', $timePeriod)
            ->where('orders.status', $status)
            ->whereDate('orders.created_at', $date)
            ->select('orders.shop', 'carts.item_number', 'carts.qty', 'carts.remarke')
            ->get();

        $orderMap = [];
        foreach ($orders as $order) {
            $orderMap[$order->shop][$order->item_number] = [
                'qty' => $order->qty,
                'remark' => $order->remarke,
            ];
        }

        return $orderMap;
    }
}
