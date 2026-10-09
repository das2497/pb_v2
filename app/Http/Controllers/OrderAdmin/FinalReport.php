<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinalReport extends Controller
{
    public function index()
    {
        $routes_normal = DB::table('routes')
            ->where('type', '=', 'Normal')
            ->where('time', '=', 'Morning')
            ->get();

        $routes_special = DB::table('routes')
            ->where('type', '=', 'Special')
            ->where('time', '=', 'Morning')
            ->get();

        $routes_pbd = DB::table('routes')
            ->where('type', '=', 'PBD')
            ->where('time', '=', 'Morning')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.final-report-morning', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function fullScreen()
    {

        $routes_normal = DB::table('routes')
            ->where('type', '=', 'Normal')
            ->where('time', '=', 'Morning')
            ->get();

        $routes_special = DB::table('routes')
            ->where('type', '=', 'Special')
            ->where('time', '=', 'Morning')
            ->get();

        $routes_pbd = DB::table('routes')
            ->where('type', '=', 'PBD')
            ->where('time', '=', 'Morning')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.final-report-morning-full-screen', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function index_evening()
    {
        $routes_normal = DB::table('routes')
            ->where('type', '=', 'Normal')
            ->where('time', '=', 'Evening')
            ->get();

        $routes_special = DB::table('routes')
            ->where('type', '=', 'Special')
            ->where('time', '=', 'Evening')
            ->get();

        $routes_pbd = DB::table('routes')
            ->where('type', '=', 'PBD')
            ->where('time', '=', 'Evening')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.final-report-evening', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }

    public function fullScreen_evening()
    {
        $routes_normal = DB::table('routes')
            ->where('type', '=', 'Normal')
            ->where('time', '=', 'Evening')
            ->get();

        $routes_special = DB::table('routes')
            ->where('type', '=', 'Special')
            ->where('time', '=', 'Evening')
            ->get();

        $routes_pbd = DB::table('routes')
            ->where('type', '=', 'PBD')
            ->where('time', '=', 'Evening')
            ->get();

        $products = DB::table('products')
            ->orderBy('item_number', 'asc')
            ->paginate(20);

        return view('order-admin.final-report-evening-full-screen', [
            'routes_normal' => $routes_normal,
            'routes_special' => $routes_special,
            'routes_pbd' => $routes_pbd,
            'products' => $products,
        ]);
    }
}
