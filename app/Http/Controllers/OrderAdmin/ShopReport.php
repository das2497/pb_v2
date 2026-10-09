<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ShopReport extends Controller
{

    public function index(Request $request)
    {
        $currentDate = $request->input('date') ?? now()->toDateString();
        $state = $request->input('state') ?? 'Processing';

        // ✅ 1. Fetch all shops once (grouped by route type)
        $shops = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->select('shops.*', 'shops.name as shop_name', 'routes.type as route_type')
            ->get()
            ->groupBy('route_type');

        // ✅ 2. Fetch all products once (paginated)
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        // ✅ 3. Fetch all orders + carts for today's morning orders at once
        $orders = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', '=', 'Morning')
            ->where('orders.status', '=', $state)
            ->whereDate('orders.created_at', '=', $currentDate)
            ->select(
                'orders.shop',
                'carts.item_number',
                'carts.qty',
                'carts.remarke'
            )
            ->get();

        // ✅ 4. Structure data for fast lookup (shop → item_number → order)
        $orderMap = [];
        foreach ($orders as $order) {
            $orderMap[$order->shop][$order->item_number] = [
                'qty' => $order->qty,
                'remark' => $order->remarke
            ];
        }

        return view('order-admin.shop-report-morning', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap
        ]);
    }



    // public function index()
    // {
    //     // Cache headers if they don't change frequently (e.g., 1 hour TTL)
    //     $header_normal = Cache::remember('header_normal_morning', 3600, function () {
    //         return Shops::join('routes', 'routes.name', '=', 'shops.morning_route')
    //             ->where('routes.type', 'Normal')
    //             ->select('shops.*', 'shops.name as shop_name')
    //             ->get();
    //     });

    //     $header_special = Cache::remember('header_special_morning', 3600, function () {
    //         return Shops::join('routes', 'routes.name', '=', 'shops.morning_route')
    //             ->where('routes.type', 'Special')
    //             ->select('shops.*', 'shops.name as shop_name')
    //             ->get();
    //     });

    //     $header_pbd = Cache::remember('header_pbd_morning', 3600, function () {
    //         return Shops::join('routes', 'routes.name', '=', 'shops.morning_route')
    //             ->where('routes.type', 'PBD')
    //             ->select('shops.*', 'shops.name as shop_name')
    //             ->get();
    //     });

    //     // Products pagination (unchanged, but could cache if static)
    //     $products = Products::orderBy('item_number', 'asc')->paginate(20);

    //     return view('order-admin.shop-report-morning', compact('products', 'header_normal', 'header_special', 'header_pbd'));
    // }

    // public function index()
    // {

    //     $header_normal = DB::table('shops')
    //         ->join('routes', 'routes.name', '=', 'shops.morning_route')
    //         ->where('routes.type', '=', 'Normal')
    //         ->select('shops.*', 'shops.name as shop_name')
    //         ->get();

    //     $header_special = DB::table('shops')
    //         ->join('routes', 'routes.name', '=', 'shops.morning_route')
    //         ->where('routes.type', '=', 'Special')
    //         ->select('shops.*', 'shops.name as shop_name')
    //         ->get();

    //     $header_pbd = DB::table('shops')
    //         ->join('routes', 'routes.name', '=', 'shops.morning_route')
    //         ->where('routes.type', '=', 'PBD')
    //         ->select('shops.*', 'shops.name as shop_name')
    //         ->get();

    //     $products = DB::table('products')
    //         ->orderBy('item_number', 'asc')
    //         ->paginate(20);

    //     return view('order-admin.shop-report-morning', [
    //         'products' => $products,
    //         'header_normal' => $header_normal,
    //         'header_special' => $header_special,
    //         'header_pbd' => $header_pbd
    //     ]);
    // }

    public function index_normal()
    {
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(10);

        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        return view('order-admin.shop-report-morning-normal', [
            'products' => $products,
            'header_normal' => $header_normal,
        ]);
    }

    public function fullScreen()
    {
        $currentDate = now()->toDateString();

        // ✅ 1. Fetch all shops once (grouped by route type)
        $shops = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->select('shops.*', 'shops.name as shop_name', 'routes.type as route_type')
            ->get()
            ->groupBy('route_type');

        // ✅ 2. Fetch all products once (paginated)
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        // ✅ 3. Fetch all orders + carts for today's morning orders at once
        $orders = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->where('orders.time_period', '=', 'Morning')
            ->where('orders.status', '=', 'Processing')
            ->whereDate('orders.created_at', '=', $currentDate)
            ->select(
                'orders.shop',
                'carts.item_number',
                'carts.qty',
                'carts.remarke'
            )
            ->get();

        // ✅ 4. Structure data for fast lookup (shop → item_number → order)
        $orderMap = [];
        foreach ($orders as $order) {
            $orderMap[$order->shop][$order->item_number] = [
                'qty' => $order->qty,
                'remark' => $order->remarke
            ];
        }

        return view('order-admin.shop-report-morning-full-screen', [
            'products' => $products,
            'header_normal' => $shops['Normal'] ?? collect(),
            'header_special' => $shops['Special'] ?? collect(),
            'header_pbd' => $shops['PBD'] ?? collect(),
            'orderMap' => $orderMap
        ]);
    }

    public function fullScreen_normal()
    {
        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(10);

        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        return view('order-admin.shop-report-morning-normal-full-screen', [
            'products' => $products,
            'header_normal' => $header_normal,
        ]);
    }

    public function back_to_report()
    {
        return redirect('/order-admin/shop-report');
    }

    public function index_evening()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.shop-report-evening', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd
        ]);
    }

    public function fullScreen_evening()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.shop-report-evening-full-screen', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd
        ]);
    }

    public function back_to_report_evening()
    {
        return redirect('/order-admin/shop-report-evening');
    }
}

