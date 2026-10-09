<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopReport extends Controller
{
    public function index()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.shop-report-morning', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd,
        ]);
    }

    public function fullScreen()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.morning_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.shop-report-morning-full-screen', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd,
        ]);
    }

    public function back_to_report()
    {
        return redirect('/rep/shop-report-morning');
    }

    public function index_evening()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.shop-report-evening', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd,
        ]);
    }

    public function fullScreen_evening()
    {
        $header_normal = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Normal')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_special = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'Special')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $header_pbd = DB::table('shops')
            ->join('routes', 'routes.name', '=', 'shops.evening_route')
            ->join('rep_assign_shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->where('routes.type', '=', 'PBD')
            ->select('shops.*', 'shops.name as shop_name')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.shop-report-evening-full-screen', [
            'products' => $products,
            'header_normal' => $header_normal,
            'header_special' => $header_special,
            'header_pbd' => $header_pbd,
        ]);
    }

    public function back_to_report_evening()
    {
        return redirect('/rep/shop-report-evening');
    }
}
