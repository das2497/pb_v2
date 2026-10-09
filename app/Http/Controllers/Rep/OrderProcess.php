<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\OrderTime;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OrderProcess extends Controller
{
    public function index(Request $request)
    {
        $Shops = DB::table('rep_assign_shops')
            ->join('shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('shops.*')
            ->get();

        $categories = Product_category::all();

        $form_token = Str::random(40);
        session()->put('form_token', $form_token);

        $selectedShop = $Shops->contains('branch_code', $request->shop)
            ? Shops::where('branch_code', $request->shop)->first()
            : null;
        $allowed_times = $selectedShop
            ? $selectedShop->allowedOrderTimes()
            : OrderTime::orderBy('id')->pluck('name')->all();
        $period = $request->input('order_time');
        if (!$selectedShop || !in_array($period, $allowed_times, true)) {
            $period = ($selectedShop && count($allowed_times) === 1) ? $allowed_times[0] : null;
        }

        if ($period === null) {
            return view('rep.create-order', [
                'category' => session('category'),
                'shops' => $Shops,
                'products' => collect(),
                'cart_item' => collect(),
                'categories' => $categories,
                'my_shop' => $request->shop,
                'form_token' => $form_token,
                'period' => null,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('order_time', $period);


        if (isset($request) && $request->has('category')) {

            $request->session()->put('category', $request->category);

            if ($request->category == '') {

                $Products = Products::forPeriod($period)->where(function ($query) use ($request) {
                    $query->where('item_number', 'like', '%' . $request->search . '%')
                        ->orWhere('category', 'like', '%' . $request->search . '%')
                        ->orWhere('name_english', 'like', '%' . $request->search . '%')
                        ->orWhere('name_sinhala', 'like', '%' . $request->search . '%');
                })
                    ->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = Cart::where('shop_bc_number', '=', $request->shop)
                    ->whereNull('order_number')
                    ->get();
            } else {

                $Products = Products::forPeriod($period)->where('category', '=', $request->category)
                    ->where(function ($query) use ($request) {
                        $query->where('item_number', 'like', '%' . $request->search . '%')
                            ->orWhere('category', 'like', '%' . $request->search . '%')
                            ->orWhere('name_english', 'like', '%' . $request->search . '%')
                            ->orWhere('name_sinhala', 'like', '%' . $request->search . '%');
                    })
                    ->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = DB::table('products')
                    ->join('carts', 'products.item_number', '=', 'carts.item_number')
                    ->where('products.category', $request->category)
                    ->where('carts.shop_bc_number', '=', $request->shop)
                    ->whereNull('order_number')
                    ->select('carts.*', 'products.*')
                    ->get();
            }

            return view('rep.create-order', [
                'category' => session('category'),
                'shops' => $Shops,
                'products' => $Products,
                'cart_item' => $cart_item,
                'input' => $request->all(),
                'categories' => $categories,
                'my_shop' => $request->shop,
                'form_token' => $form_token,
                'period' => $period,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('category', 'CAKE ITEMS');

        $Products = Products::forPeriod($period)->where('category', '=', 'CAKE ITEMS')
            ->orderBy('item_number', 'asc')
            ->get();

        $cart_item = DB::table('products')
            ->join('carts', 'products.item_number', '=', 'carts.item_number')
            ->where('products.category', 'CAKE ITEMS')
            ->where('carts.shop_bc_number', '=', $request->shop)
            ->whereNull('order_number')
            ->select('carts.*', 'products.*')
            ->get();

        return view('rep.create-order', [
            'category' =>  session('category'),
            'shops' => $Shops,
            'cart_item' => $cart_item,
            'products' => $Products,
            'categories' => $categories,
            'my_shop' => $request->shop,
            'form_token' => $form_token,
            'period' => $period,
            'allowed_times' => $allowed_times,
        ]);
    }

    public function add_to_cart(Request $request)
    {
        $request->validate([
            'qty' => 'required',
            'branch_code' => 'required'
        ]);

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $cart_item = Cart::where('shop_bc_number', '=', $request->shop)
            ->whereNull('order_number')
            ->get();

        Session::put([
            'cart_item' => $cart_item,
        ]);

        $price = $product->priceFor($shop);

        Cart::create([
            'item_number' => $request->item_number,
            'qty' => $request->qty,
            'price' => $price,
            'shop_bc_number' => $request->branch_code,
        ]);

        return back()
            ->with('success', 'Product added to cart successfully!');
    }

    public function addAllToCart(Request $request)
    {

        $request->validate([
            'form_token' => 'required',
        ]);

        if ($request->form_token !== session('form_token')) {
            return redirect()->back()->withErrors(['error' => 'Duplicate submission detected.']);
        }

        session()->forget('form_token');

        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');


        // dd('Selected Items:', $selectedItems, 'Quantities:', $quantities, 'Branch Code:', $branchCode);


        // Get the shop details based on the branch code
        $shop = Shops::where('branch_code', '=', $branchCode)->first();

        // Loop through each selected item
        foreach ($selectedItems as $itemNumber) {

            $cartHas = DB::table('carts')
                ->where('item_number', $itemNumber)
                ->where('shop_bc_number', $branchCode)
                ->whereNull('order_number')
                ->whereNull('default_name')
                ->exists();

            if ($cartHas) {
                return back()->with('error', 'Cart already have this item');
            }

            // Get the quantity for the current item
            $qty = $quantities[$itemNumber];

            if ($qty > 0) {
                // Get the product details based on the item number
                $product = Products::where('item_number', $itemNumber)->first();

                // Get the current cart items for the shop, where the order number is null
                $cart_item = Cart::where('shop_bc_number', $branchCode)
                    ->whereNull('order_number')
                    ->get();

                // Store the current cart items in the session
                Session::put([
                    'cart_item' => $cart_item,
                ]);

                // Determine the price based on the shop's price range
                $price = $product->priceFor($shop);

                // Add the item to the cart
                Cart::create([
                    'item_number' => $itemNumber,
                    'qty' => $qty,
                    'price' => $price,
                    'shop_bc_number' => $branchCode,
                ]);
            }
        }

        $shop_name = Shops::where('branch_code', '=', $branchCode)->first()->name;

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Rep add to cart' . implode(' ', $selectedItems) . ' items to [' . $branchCode . ']' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Selected items added to cart successfully!');
    }

    public function clearcart(Request $request)
    {

        Cart::where('shop_bc_number', $request->shop)
            ->whereNull('order_number')
            ->whereNull('default_name')
            ->delete();

        return back()->with('success', 'Successfully cleared!');
    }
}
