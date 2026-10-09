<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnderReviewOrdersAddItems extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {

        $categories = Product_category::all();
        $period = Orders::where('unique_id', '=', $request->order_number)->value('time_period');

        if ($request->category != '') {
            $cart_items = DB::table('carts')
                ->join('products', 'carts.item_number', '=', 'products.item_number')
                ->where('carts.order_number', '=', $request->order_number)
                ->select('products.*', 'carts.*')
                ->get();

            $shop = Orders::where('unique_id', '=', $request->order_number)->select('shop')->first();

            $products = Products::forPeriod($period)->where('category', '=', $request->category)
                ->orderBy('item_number')
                ->get();

            $request->session()->put('category', $request->category);

            return view('rep.under-review-orders-add-items', [
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

        $products = Products::forPeriod($period)->where('category', '=', 'CAKE ITEMS')
            ->orderBy('item_number')
            ->get();

        $request->session()->put('category', 'CAKE ITEMS');

        return view('rep.under-review-orders-add-items', [
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

        $price = $product->priceFor($shop);

        $this->cartService->addItemToExistingOrder($request->order_number, $request->item_number, $request->shop, $price, $request->qty);

        return back()
            ->with('success', 'Product added to under review order successfully!');
    }
}

