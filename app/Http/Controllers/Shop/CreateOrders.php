<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class CreateOrders extends Controller
{
    public function index(Request $request)
    {
        $Shops = Shops::all();
        $my_shop = Shops::where('email', '=', Auth::user()->email)->first();
        $shop_bc_number = $my_shop->branch_code;

        $allowed_times = $my_shop->allowedOrderTimes();
        $period = $request->input('order_time');
        if (!in_array($period, $allowed_times, true)) {
            $period = count($allowed_times) === 1 ? $allowed_times[0] : null;
        }

        $categories = Product_category::all();

        if ($period === null) {
            return view('shop.create-order', [
                'my_shop' => $shop_bc_number,
                'category' => session('category'),
                'shops' => $Shops,
                'products' => collect(),
                'cart_item' => collect(),
                'categories' => $categories,
                'period' => null,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('order_time', $period);

        $cart_item = null;
        $Products = null;

        if (isset($request) && $request->has('category')) {

            $request->session()->put('category', $request->category);

            if ($request->category == '') {

                if ($request->has('search') && $request->search != '') {
                    $Products = Products::forPeriod($period)->where(function ($query) use ($request) {
                        $query->where('item_number', 'like', '%' . $request->search . '%')
                            ->orWhere('name_english', 'like', '%' . $request->search . '%')
                            ->orWhere('name_sinhala', 'like', '%' . $request->search . '%')
                            ->orWhere('category', 'like', '%' . $request->search . '%');
                    })
                        ->where('visibility', 'All')
                        ->orderBy('item_number', 'asc')
                        ->get();
                } else {

                    $Products = Products::forPeriod($period)->where('visibility', '=', 'All')
                        ->orderBy('item_number', 'asc')
                        ->get();
                }

                $cart_item = Cart::where('shop_bc_number', '=', $shop_bc_number)
                    ->whereNull('order_number')
                    ->get();
            } else {

                if ($request->has('search') && $request->search != '') {
                    $Products = Products::forPeriod($period)->where('category', $request->category)
                        ->where('visibility', 'All')
                        ->where(function ($query) use ($request) {
                            $query->where('item_number', 'like', '%' . $request->search . '%')
                                ->orWhere('name_english', 'like', '%' . $request->search . '%')
                                ->orWhere('name_sinhala', 'like', '%' . $request->search . '%')
                                ->orWhere('category', 'like', '%' . $request->search . '%');
                        })
                        ->orderBy('item_number', 'asc')
                        ->get();
                } else {

                    $Products = Products::forPeriod($period)->where('category', '=', $request->category)
                        ->where('visibility', '=', 'All')
                        ->orderBy('item_number', 'asc')
                        ->get();
                }

                $cart_item = DB::table('products')
                    ->join('carts', 'products.item_number', '=', 'carts.item_number')
                    ->where('products.category', $request->category)
                    ->where('carts.shop_bc_number', '=', $shop_bc_number)
                    ->whereNull('order_number')
                    ->select('carts.*', 'products.*')
                    ->get();
            }

            return view('shop.create-order', [
                'my_shop' => $shop_bc_number,
                'category' => session('category'),
                'shops' => $Shops,
                'products' => $Products,
                'cart_item' => $cart_item,
                'input' => $request->all(),
                'categories' => $categories,
                'period' => $period,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('category', 'CAKE ITEMS');

        if ($request->has('search')) {
            $Products = Products::forPeriod($period)->where('category', 'CAKE ITEMS')
                ->where('visibility', 'All')
                ->where(function ($query) use ($request) {
                    $query->where('item_number', 'like', '%' . $request->search . '%')
                        ->orWhere('name_english', 'like', '%' . $request->search . '%')
                        ->orWhere('name_sinhala', 'like', '%' . $request->search . '%')
                        ->orWhere('category', 'like', '%' . $request->search . '%');
                })
                ->orderBy('item_number', 'asc')
                ->get();
        } else {
            $Products = Products::forPeriod($period)->where('category', '=', 'CAKE ITEMS')
                ->where('visibility', '=', 'All')
                ->orderBy('item_number', 'asc')
                ->get();
        }

        $cart_item = DB::table('products')
            ->join('carts', 'products.item_number', '=', 'carts.item_number')
            ->where('products.category', 'CAKE ITEMS')
            ->where('carts.shop_bc_number', '=', $shop_bc_number)
            ->whereNull('order_number')
            ->select('carts.*', 'products.*')
            ->get();

        return view('shop.create-order', [
            'my_shop' => $shop_bc_number,
            'category' =>  session('category'),
            'shops' => $Shops,
            'cart_item' => $cart_item,
            'products' => $Products,
            'categories' => $categories,
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

        // Prevent duplicate orders by checking a unique token----------------------------------------------------------------------------------------
        if (Cache::has('add_to_cart_token')) {
            return back()->with('error', 'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.');
        }
        // Set a unique token in the session to prevent multiple submissions
        // Store the token in the cache with a 2-second expiration
        Cache::put('add_to_cart_token', Str::random(40), 2); // Expires after 2 seconds
        //--------------------------------------------------------------------------------------------------------------------------------------------

        $cartHas = Cart::where('item_number', '=', $request->item_number)
            ->where('shop_bc_number', $request->branch_code)
            ->whereNotNull('default_name')
            ->whereNull('order_number')
            ->count();

        if ($cartHas) {
            return back()->with('error', 'Cart already have this item');
        }

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $cart_item = Cart::where('shop_bc_number', '=', $request->shop)
            ->whereNull('order_number')
            ->get();

        Session::put([
            'cart_item' => $cart_item,
        ]);

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
            'shop_bc_number' => $request->branch_code,
        ]);

        return back()
            ->with('success', 'Product added to cart successfully........!');
    }

    public function addAllToCart2(Request $request)
    {
        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');

        // Use dd() to output the values and stop further execution
        dd('Selected Items:', $selectedItems, 'Quantities:', $quantities, 'Branch Code:', $branchCode);


        // foreach ($selectedItems as $itemNumber) {
        //     $qty = $quantities[$itemNumber] ?? 1;
        //     // Add the item to the cart with the given quantity
        //     // Example:
        //     Cart::add($itemNumber, $qty, $branchCode);
        // }

        // return redirect()->back()->with('success', 'Selected items added to cart successfully! ' . implode(" ",$selectedItems));
    }

    public function addAllToCart(Request $request)
    {

        // Prevent duplicate orders by checking a unique token----------------------------------------------------------------------------------------
        if (Cache::has('addAllToCart_token')) {
            return back()->with('error', 'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.');
        }
        // Set a unique token in the session to prevent multiple submissions
        // Store the token in the cache with a 2-second expiration
        Cache::put('addAllToCart_token', Str::random(40), 2); // Expires after 2 seconds
        //--------------------------------------------------------------------------------------------------------------------------------------------

        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');

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
                $price = '';
                if ($shop->price_range == 'Unit Price') {
                    $price = $product->unit_price;
                } elseif ($shop->price_range == 'PB MRP') {
                    $price = $product->mrp;
                } elseif ($shop->price_range == 'PB Direct Sale Price') {
                    $price = $product->direct_sale_price;
                }

                // Add the item to the cart
                Cart::create([
                    'item_number' => $itemNumber,
                    'qty' => $qty,
                    'price' => $price,
                    'shop_bc_number' => $branchCode,
                ]);
            }
        }

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Shop add to cart items : ' . implode(' ', $selectedItems),
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Selected items added to cart successfully!');
    }
}
