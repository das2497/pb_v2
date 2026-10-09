<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinalReport extends Controller
{
    public function index()
    {
        $routes_normal = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Normal')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_special = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Special')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_pbd = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'PBD')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.final-report-morning', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function fullScreen()
    {
        $routes_normal = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Normal')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_special = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Special')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_pbd = DB::table('routes')
            ->join('shops', 'shops.morning_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'PBD')
            ->where('routes.time', '=', 'Morning')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.final-report-morning-full-screen', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function index_evening()
    {
        $routes_normal = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Normal')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_special = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Special')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_pbd = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'PBD')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.final-report-evening', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function fullScreen_evening()
    {
        $routes_normal = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Normal')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_special = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'Special')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $routes_pbd = DB::table('routes')
            ->join('shops', 'shops.evening_route', '=', 'routes.name')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('routes.type', '=', 'PBD')
            ->where('routes.time', '=', 'Evening')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('routes.name', 'routes.id')  // Include routes.id in the select clause
            ->distinct('routes.name')
            ->orderBy('routes.id')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('rep.final-report-evening-full-screen', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }
}
