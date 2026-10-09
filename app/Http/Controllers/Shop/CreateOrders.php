<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\Cart;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrders extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

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
        $request->validate((new AddToCartRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        try {
            $this->cartService->addToCart($shop, $product, (int) $request->qty, $request->remarke);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', 'Product added to cart successfully........!');
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
            $this->cartService->addAllToCart($shop, $items, Auth::user()->name);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Selected items added to cart successfully!');
    }
}
