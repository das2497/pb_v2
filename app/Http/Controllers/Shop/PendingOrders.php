<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PendingOrders extends Controller
{
    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('orders.status', '=', 'Pending')
            ->where('shops.email', '=', Auth::user()->email)
            ->select('orders.*', 'shops.*', 'orders.created_at as created_time')
            ->get();

        return view('shop.pending-orders', [
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

        $shop = Orders::where('unique_id', '=', $request->id)->first()->shop;

        return view('shop.pending-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'qty' => 'required',
        ]);

        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = '';
        if ($shop->price_range == 'Unit Price') {
            $price = $product->unit_price;
        } elseif ($shop->price_range == 'PB MRP') {
            $price = $product->mrp;
        } elseif ($shop->price_range == 'PB Direct Sale Price') {
            $price = $product->direct_sale_price;
        }

        $qtyCart = Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->first()->qty;

        Orders::where('unique_id', '=', $request->order_number)
            ->increment('total_price', $price * ($request->qty - $qtyCart));

        Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->update([
                'qty' => $request->qty,
                'remarke' => $request->remarke
            ]);

        Logs::create([
            'type' => 'Update item pending Order',
            'message' => 'Shop updated item : [' . $request->item_number . '] quantity : [' . $request->qty . '] remark : [' . $request->remarke . '] in pending order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Quantity Updated Successfully');
    }

    public function delete(Request $request)
    {
        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = '';
        if ($shop->price_range == 'Unit Price') {
            $price = $product->unit_price;
        } elseif ($shop->price_range == 'PB MRP') {
            $price = $product->mrp;
        } elseif ($shop->price_range == 'PB Direct Sale Price') {
            $price = $product->direct_sale_price;
        }

        Orders::where('unique_id', '=', $request->order_number)
            ->decrement('total_price', $price * $request->qty);

        Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->delete();

        Logs::create([
            'type' => 'Delete item pending Order',
            'message' => 'Shop deleted item : [' . $request->item_number . '] from pending order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Successfully deleted!');
    }
}

