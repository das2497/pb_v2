<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Processing_orders_add_item extends Controller
{
    public function index(Request $request)
    {

        $categories = Product_category::all();
        $period = Orders::where('unique_id', '=', $request->order_number)->value('time_period');

        if (isset($request) && $request->has('category') && $request->category != '') {
            $cart_items = DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->where('carts.order_number', '=', $request->order_number)
                ->select('products.*', 'carts.*')
                ->get();

            $shop = Orders::where('unique_id', '=', $request->order_number)->select('shop')->first();

            $products = Products::forPeriod($period)->where('category', '=', $request->category)->get();

            $request->session()->put('category', $request->category);

            return view('order-admin.processing-orders-add-items', [
                'category' => session('category'),
                'cart_items' => $cart_items,
                'products' => $products,
                'order_number' => $request->order_number,
                'shop' => $shop->shop,
                'categories' => $categories,
            ]);
        }

        if (isset($request) && $request->has('category') && $request->category == '') {
            $cart_items = DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->where('carts.order_number', '=', $request->order_number)
                ->select('products.*', 'carts.*')
                ->get();

            $shop = Orders::where('unique_id', '=', $request->order_number)->select('shop')->first();

            $products = Products::forPeriod($period)->where('category', '=', $request->category)->get();

            $request->session()->put('category', $request->category);

            return view('order-admin.processing-orders-add-items', [
                'category' => session('category'),
                'cart_items' => $cart_items,
                'products' => $products,
                'order_number' => $request->order_number,
                'shop' => $shop->shop,
                'categories' => $categories,
            ]);
        }

        $cart_items = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number')
            ->where('carts.order_number', '=', $request->order_number)
            ->select('products.*', 'carts.*')
            ->get();

        $shop = Orders::where('unique_id', '=', $request->order_number)->select('shop')->first();

        $products = Products::forPeriod($period)->where('category', '=', 'CAKE ITEMS')->get();

        $request->session()->put('category', 'CAKE ITEMS');

        return view('order-admin.processing-orders-add-items', [
            'category' => session('category'),
            'cart_items' => $cart_items,
            'products' => $products,
            'order_number' => $request->order_number,
            'shop' => $shop->shop,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'qty' => 'required',
        ]);

        if (!Products::availableForOrder($request->item_number, $request->order_number)) {
            return back()->with('error', 'This product is not available for this order\'s time period.');
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

        Cart::create([
            'item_number' => $request->item_number,
            'qty' => $request->qty,
            'price' => $price,
            'shop_bc_number' => $request->shop,
            'order_number' => $request->order_number,
        ]);

        Orders::where('unique_id', '=', $request->order_number)
            ->increment('total_price', $price * $request->qty);

        $shop_name = Shops::where('branch_code', '=', $request->shop)->first()->name;
        Logs::create([
            'type' => 'Add To processing order',
            'message' => 'Order admin add item ' . $request->item_number . ' quantity ' . $request->qty . ' to processing order ' . $request->order_number . ' to ' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Product added to pending order successfully!');
    }
}
