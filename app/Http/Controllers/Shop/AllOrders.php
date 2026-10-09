<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AllOrders extends Controller
{
    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('shops.email', '=', Auth::user()->email)
            ->select('orders.*', 'shops.*', 'orders.created_at as date')
            ->orderBy('orders.created_at')
            ->get();

        return view('shop.all-orders', [
            'Orders' => $Orders
        ]);
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

        return view('shop.all-orders-view', [
            'items' => $items,
            'order_number' => $request->id
        ]);
    }
}
