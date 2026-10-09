<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompleteOrders extends Controller
{
    public function index(Request $request)
    {
        $Orders = null;
        if ($request->has('search') && $request->search != '') {
            if ($request->date == null) {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->where(function ($query) use ($request) {
                        $query->where('orders.unique_id', 'like', '%' . $request->search . '%');
                    })
                    ->where('orders.status', '=', 'Complete')
                    ->where('shops.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();
            } else {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->where(function ($query) use ($request) {
                        $query->where('orders.unique_id', 'like', '%' . $request->search . '%');
                    })
                    ->whereDate('orders.created_at', $request->date)
                    ->where('orders.status', '=', 'Complete')
                    ->where('shops.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();
            }
        } else {
            if ($request->date == null) {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->where('orders.status', '=', 'Complete')
                    ->where('shops.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();
            } else {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->whereDate('orders.created_at', $request->date)
                    ->where('orders.status', '=', 'Complete')
                    ->where('shops.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->get();
            }
        }

        return view('shop.complete-orders', [
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

        return view('shop.complete-orders-view', [
            'items' => $items,
            'order_number' => $request->id
        ]);
    }
}
