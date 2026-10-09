<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnderReviewOrders extends Controller
{
    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('orders.status', '=', 'Under Review')
            ->where('shops.email', '=', Auth::user()->email)
            ->get();

        return view('shop.under-review-orders', [
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

        return view('shop.under-review-orders-view', [
            'items' => $items,
            'order_number' => $request->id
        ]);
    }
}

