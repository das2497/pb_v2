<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pending_orders_add_item extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

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

            if ($request->search != '') {
                $products = Products::forPeriod($period)->where('category', '=', $request->category)
                    ->where('name_english', 'like', '%' . $request->search . '%')
                    ->get();
            } else {
                $products = Products::forPeriod($period)->where('category', '=', $request->category)->get();
            }

            $request->session()->put('category', $request->category);

            return view('order-admin.pending-orders-add-items', [
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

            if ($request->search != '') {
                $products = Products::forPeriod($period)->where('name_english', 'like', '%' . $request->search . '%')->get();
            } else {
                $products = Products::forPeriod($period)->get();
            }

            $request->session()->put('category', $request->category);

            return view('order-admin.pending-orders-add-items', [
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

        return view('order-admin.pending-orders-add-items', [
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
        $request->validate((new UpdateOrderItemQuantityRequest())->rules());

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
            'message' => 'Order admin add item ' . $request->item_number . ' quantity ' . $request->qty . ' to pending order ' . $request->order_number . ' to ' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Product added to pending order successfully!');
    }
}
