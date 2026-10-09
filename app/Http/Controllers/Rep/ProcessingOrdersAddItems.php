<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcessingOrdersAddItems extends Controller
{
    public function index(Request $request)
    {

        $categories = Product_category::all();
        $period = Orders::where('unique_id', '=', $request->order_number)->value('time_period');
        $shop = Orders::where('unique_id', '=', $request->order_number)->select('shop')->first();

        $products = null;

        if (isset($request) && $request->has('category')) {

            $request->session()->put('category', $request->category);

            $cart_item = DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->where('carts.order_number', '=', $request->order_number)
                ->select('products.*', 'carts.*')
                ->get();

            if ($request->category == '') {
                if ($request->search != '') {
                    $products = Products::forPeriod($period)->where(function ($query) use ($request) {
                        $query->where('item_number', 'like', '%' . $request->search . '%')
                            ->orWhere('name_english', 'like', '%' . $request->search . '%');
                    })
                        ->orderBy('item_number')
                        ->get();

                    // dd($products);
                } else {
                    $products = Products::forPeriod($period)->orderBy('item_number')
                        ->get();
                }

            } else {
                if ($request->search != '') {
                    // dd('category : '.$request->category.'. search : '.$request->search);
                    $products = Products::forPeriod($period)->where('category', '=', $request->category)
                        ->where(function ($query) use ($request) {
                            $query->where('item_number', 'like', '%' . $request->search . '%')
                                ->orWhere('name_english', 'like', '%' . $request->search . '%');
                        })
                        ->orderBy('item_number')
                        ->get();

                    // dd($products);
                } else {
                    $products = Products::forPeriod($period)->where('category', '=', $request->category)
                        ->orderBy('item_number')
                        ->get();
                }
            }

            // dd($products);

            return view('rep.processing-orders-add-items', [
                'category' => session('category'),
                'products' => $products,
                'cart_items' => $cart_item,
                'order_number' => $request->order_number,
                'shop' => $shop->shop,
                'categories' => $categories,
            ]);
        }

        $cart_item = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number')
            ->where('carts.order_number', '=', $request->order_number)
            ->select('products.*', 'carts.*')
            ->get();

        $products = Products::forPeriod($period)->where('category', '=', 'CAKE ITEMS')
            ->orderBy('item_number')
            ->get();

        $request->session()->put('category', 'CAKE ITEMS');

        // dd($products);

        return view('rep.processing-orders-add-items', [
            'category' => session('category'),
            'products' => $products,
            'cart_items' => $cart_item,
            'order_number' => $request->order_number,
            'shop' => $shop->shop,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {

        // dd('shop',$request->shop,'item number ',$request->item_number,'order number ',$request->order_number,'qty',$request->qty);

        $rep = Rep::where('email', '=', Auth::user()->email)->first();

        if ($rep->access == 'off') {
            return back()
                ->with('error', 'You do not have access to create orders.');
        }

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
            'message' => 'Rep add item ' . $request->item_number . ' quantity ' . $request->qty . ' to processing order ' . $request->order_number . ' to ' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Product added to pending order successfully!');
    }
}
