<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopReport extends Controller
{

    public function index(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteType('morning_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('order-admin.shop-report-morning', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function index_normal()
    {
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();

        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $currentDate = now()->toDateString();
        $orderMap = $this->buildOrderMap('Morning', 'Complete', $currentDate);
        $noteWords = $this->buildNoteWordsMap('Morning', 'Complete', $currentDate);

        return view('order-admin.shop-report-morning-normal', [
            'products' => $products,
            'header_normal' => $header_normal,
            'orderMap' => $orderMap,
            'noteWords' => $noteWords,
        ]);
    }

    public function fullScreen(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteType('morning_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('order-admin.shop-report-morning-full-screen', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function fullScreen_normal(Request $request)
    {
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();

        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Complete';
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);
        $noteWords = $this->buildNoteWordsMap('Morning', $state, $currentDate);

        return view('order-admin.shop-report-morning-normal-full-screen', [
            'products' => $products,
            'header_normal' => $header_normal,
            'orderMap' => $orderMap,
            'noteWords' => $noteWords,
            'currentDate' => $currentDate,
            'state' => $state,
        ]);
    }

    public function back_to_report()
    {
        return redirect('/order-admin/shop-report');
    }

    public function index_evening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteType('evening_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('order-admin.shop-report-evening', [
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

        $shops = $this->shopsByRouteType('evening_route');
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('order-admin.shop-report-evening-full-screen', [
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
        return redirect('/order-admin/shop-report-evening');
    }

    /**
     * Full (unpaginated) morning report table, for "Export to Excel" on the
     * main shop-report page. Honors the same date/state filters as index()
     * so the export matches whatever the admin currently has on screen.
     */
    public function exportMorning(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteType('morning_route');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildOrderMap('Morning', $state, $currentDate);

        return view('order-admin.partials.shop-report-export-table', [
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
     * index_evening() so the export matches whatever the admin currently
     * has on screen.
     */
    public function exportEvening(Request $request)
    {
        $currentDate = $request->filled('date') ? $request->input('date') : now()->toDateString();
        $state = $request->filled('state') ? $request->input('state') : 'Processing';

        $shops = $this->shopsByRouteType('evening_route');
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();
        $orderMap = $this->buildOrderMap('Evening', $state, $currentDate);

        return view('order-admin.partials.shop-report-export-table', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap,
        ]);
    }

    /**
     * Full (unpaginated) morning-normal report table, for "Export to Excel"
     * on the normal-routes-only shop report page.
     */
    public function exportMorningNormal()
    {
        $products = DB::table('products')->orderBy('item_number', 'asc')->get();

        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $currentDate = now()->toDateString();
        $orderMap = $this->buildOrderMap('Morning', 'Complete', $currentDate);
        $noteWords = $this->buildNoteWordsMap('Morning', 'Complete', $currentDate);

        return view('order-admin.partials.shop-report-normal-export-table', [
            'products' => $products,
            'header_normal' => $header_normal,
            'orderMap' => $orderMap,
            'noteWords' => $noteWords,
        ]);
    }

    /**
     * Shops joined to their route for the given route-time column (morning_route /
     * evening_route), grouped by route type (Normal / Special / PBD) in one query
     * instead of one query per route type.
     */
    private function shopsByRouteType(string $routeColumn)
    {
        return DB::table('shops')
            ->join('routes', 'routes.name', '=', "shops.$routeColumn")
            ->select('shops.*', 'shops.name as shop_name', 'routes.type as route_type')
            ->get()
            ->groupBy('route_type');
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
                'remark' => $order->remarke
            ];
        }

        return $orderMap;
    }

    /**
     * Fetches each shop's order note for the day once and structures it as
     * shop => [word, word, ...], matching the per-shop ->first() row the old
     * per-cell query used (lowest id per shop, taken deterministically here
     * instead of leaving it to an unordered query run once per table cell).
     */
    private function buildNoteWordsMap(string $timePeriod, string $status, string $date): array
    {
        $notes = DB::table('orders')
            ->where('status', $status)
            ->where('time_period', $timePeriod)
            ->whereDate('created_at', $date)
            ->orderBy('id')
            ->select('shop', 'note')
            ->get();

        $noteWords = [];
        foreach ($notes as $row) {
            if (!array_key_exists($row->shop, $noteWords)) {
                $noteWords[$row->shop] = explode(' ', $row->note ?? '');
            }
        }

        return $noteWords;
    }

}
