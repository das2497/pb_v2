<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\OrderTime;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepCreateOrder extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        $Shops = Shops::all()->sortBy('name ASC');
        $categories = Product_category::all();

        $selectedShop = $request->shop ? Shops::where('branch_code', $request->shop)->first() : null;
        $allowed_times = $selectedShop
            ? $selectedShop->allowedOrderTimes()
            : OrderTime::orderBy('id')->pluck('name')->all();
        $period = $request->input('order_time');
        if (!$selectedShop || !in_array($period, $allowed_times, true)) {
            $period = ($selectedShop && count($allowed_times) === 1) ? $allowed_times[0] : null;
        }

        if ($period === null) {
            return view('order-admin.create-order', [
                'category' => session('category'),
                'shops' => $Shops,
                'products' => collect(),
                'cart_item' => collect(),
                'categories' => $categories,
                'my_shop' => $request->shop,
                'period' => null,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('order_time', $period);

        if (isset($request) && $request->has('category') && $request->search == '') {

            $request->session()->put('category', $request->category);

            if ($request->category == '') {

                $Products = Products::forPeriod($period)->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = Cart::where('shop_bc_number', '=', $request->shop)
                    ->whereNull('order_number')
                    ->get();
            } else {

                $Products = Products::forPeriod($period)->where('category', '=', $request->category)
                    ->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = DB::table('products')
                    ->join('carts', 'products.item_number', '=', 'carts.item_number')
                    ->where('products.category', $request->category)
                    ->where('carts.shop_bc_number', '=', $request->shop)
                    ->whereNull('carts.order_number')
                    ->select('carts.*', 'products.*')
                    ->get();
            }

            return view('order-admin.create-order', [
                'category' => session('category'),
                'shops' => $Shops,
                'products' => $Products,
                'cart_item' => $cart_item,
                'input' => $request->all(),
                'categories' => $categories,
                'my_shop' => $request->shop,
                'period' => $period,
                'allowed_times' => $allowed_times,
            ]);
        }

        if (isset($request) && $request->has('category') && $request->search != '') {

            $request->session()->put('category', $request->category);

            if ($request->category == '') {

                $Products = Products::forPeriod($period)->where('name_english', 'like', '%' . $request->search . '%')
                    ->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = Cart::where('shop_bc_number', '=', $request->shop)
                    ->whereNull('order_number')
                    ->get();
            } else {

                $Products = Products::forPeriod($period)->where('category', '=', $request->category)
                    ->where('name_english', 'like', '%' . $request->search . '%')
                    ->orderBy('item_number', 'asc')
                    ->get();

                $cart_item = DB::table('products')
                    ->join('carts', 'products.item_number', '=', 'carts.item_number')
                    ->where('products.category', $request->category)
                    ->where('carts.shop_bc_number', '=', $request->shop)
                    ->whereNull('carts.order_number')
                    ->select('carts.*', 'products.*')
                    ->get();
            }

            return view('order-admin.create-order', [
                'category' => session('category'),
                'shops' => $Shops,
                'products' => $Products,
                'cart_item' => $cart_item,
                'input' => $request->all(),
                'categories' => $categories,
                'my_shop' => $request->shop,
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
            ->whereNull('carts.order_number')
            ->select('carts.*', 'products.*')
            ->get();

        return view('order-admin.create-order', [
            'category' =>  session('category'),
            'shops' => $Shops,
            'cart_item' => $cart_item,
            'products' => $Products,
            'categories' => $categories,
            'my_shop' => $request->shop,
            'period' => $period,
            'allowed_times' => $allowed_times,
        ]);
    }

    public function add_to_cart(Request $request)
    {
        $request->validate((new AddToCartRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        try {
            $this->cartService->addItemToCart($shop, $product, (int) $request->qty);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', 'Product added to cart successfully!');
    }

    public function addAllToCart(Request $request)
    {
        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');
        $shop = Shops::where('branch_code', '=', $branchCode)->first();

        $items = array_map(
            fn ($itemNumber) => ['item_number' => $itemNumber, 'qty' => (int) ($quantities[$itemNumber] ?? 0)],
            $selectedItems
        );

        try {
            $this->cartService->addAllItemsToCart($shop, $items);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Order admin add to cart items ' . implode(' ', $selectedItems) . ' to ' . $branchCode,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Selected items added to cart successfully!');
    }
}
