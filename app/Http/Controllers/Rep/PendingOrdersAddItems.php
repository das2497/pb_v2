<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Rep;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PendingOrdersAddItems extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

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

            return view('rep.pending-orders-add-items', [
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

        return view('rep.pending-orders-add-items', [
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

        $shop_name = Shops::where('branch_code', '=', $request->shop)->first()->name;
        Logs::create([
            'type' => 'Add To pending order',
            'message' => 'Rep add item ' . $request->item_number . ' quantity ' . $request->qty . ' to pending order ' . $request->order_number . ' to ' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Product added to pending order successfully!');
    }
}
