<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcessingOrders extends Controller
{

    public function index(Request $request)
    {

        $Orders = null;
        if ($request->has('search') && $request->search != '') {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->where('orders.status', '=', 'Processing')
                ->where('reps.email', '=', Auth::user()->email)
                ->where(function ($query) use ($request) {
                    $query->where('orders.unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('shops.name', 'like', '%' . $request->search . '%');
                })
                ->select('orders.*', 'shops.name as shop_name', 'shops.branch_code as shop')
                ->orderBy('orders.created_at', 'asc')
                ->get();
        } else {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                ->join('reps', 'rep_assign_shops.rep_id', '=', 'reps.id')
                ->where('orders.status', '=', 'Processing')
                ->where('reps.email', '=', Auth::user()->email)
                ->select('orders.*', 'shops.name as shop_name', 'shops.branch_code as shop')
                ->orderBy('orders.created_at', 'asc')
                ->get();
        }

        return view('rep.processing-order', [
            'Orders' => $Orders
        ]);
    }

    public function note_update(Request $request)
    {
        $rep = Rep::where('email', '=', Auth::user()->email)->first();

        if ($rep->access == 'off') {
            return back()->with('error', 'You do not have access to update processing order note.');
        }

        Orders::where('unique_id', '=', $request->order_number)
            ->update(['note' => $request->note]);

        Logs::create([
            'type' => 'Update Processing Order Note',
            'message' => 'Rep updated note : [' . $request->note . '] in processing order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Note Updated Successfully');
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->leftJoin('product_categories', 'product_categories.category', '=', 'products.category')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

        $shop = Orders::where('unique_id', '=', $request->id)->first()->shop;

        $shop_name = Shops::where('branch_code', '=', $shop)->first();

        return view('rep.processing-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop,
            'shop_name' => $shop_name->name,
            'order_note' => $request->note
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

        Logs::create([
            'type' => 'Update item processing Order',
            'message' => 'Rep updated item : [' . $request->item_number . '] quantity : [' . $request->qty . '] remark : [' . $request->remarke . '] in processing order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Quantity Updated Successfully');
    }

    public function delete(Request $request)
    {

        $rep = Rep::where('email', '=', Auth::user()->email)->first();

        if ($rep->access == 'off') {
            return back()
                ->with('error', 'You do not have access to create orders.');
        }

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
            'type' => 'Delete item processing Order',
            'message' => 'Rep deleted item : [' . $request->item_number . '] from processing order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Successfully deleted!');
    }
}
