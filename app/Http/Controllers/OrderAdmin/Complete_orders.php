<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Complete_orders extends Controller
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
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            } else {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->where(function ($query) use ($request) {
                        $query->where('orders.unique_id', 'like', '%' . $request->search . '%');
                    })
                    ->whereDate('orders.created_at', $request->date)
                    ->where('orders.status', '=', 'Complete')
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            }

            return view('order-admin.complete-orders', [
                'Orders' => $Orders
            ]);
        } else {
            if ($request->date == null) {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->where('orders.status', '=', 'Complete')
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            } else {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->whereDate('orders.created_at', $request->date)
                    ->where('orders.status', '=', 'Complete')
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            }

            return view('order-admin.complete-orders', [
                'Orders' => $Orders
            ]);
        }
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

        $order = Orders::where('unique_id', '=', $request->id)->first();

        $shop = Shops::where('branch_code', '=', $order->shop)->first();

        return view('order-admin.complete-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop->name,
            'order_created' => $order->created_at
        ]);
    }
}

