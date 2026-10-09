<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnderReviewOrders extends Controller
{
    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('status', '=', 'Under Review')
            ->select('orders.*', 'shops.name as shop_name', 'shops.branch_code as shop')
            ->orderBy('orders.created_at', 'asc')
            ->get();

        return view('rep.under-review-order', [
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

        return view('rep.under-review-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop
        ]);
    }

    public function update(Request $request)
    {
        $rep = Rep::where('email', '=', Auth::user()->email)->first();

        if ($rep->access == 'off') {
            return back()
                ->with('error', 'You do not have access to create orders.');
        }
        
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

        return back()
            ->with('success', 'Successfully deleted!');
    }

    public function delete_order(Request $request)
    {
        DB::table('carts')
            ->where('order_number', '=', $request->id)
            ->delete();

        DB::table('orders')
            ->where('unique_id', '=', $request->id)
            ->delete();

        return back()
            ->with('success', 'Order Deleted Successfully');
    }
}

