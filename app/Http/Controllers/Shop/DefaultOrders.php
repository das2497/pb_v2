<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultOrders extends Controller
{
    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('orders.status', '=', 'Default')
            ->where('shops.email', '=', Auth::user()->email)
            ->get();

        return view('shop.default-orders', [
            'Orders' => $Orders
        ]);
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

            $branch_code = Shops::where('email','=',Auth::user()->email)->first()->branch_code;

        return view('shop.default-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'branch_code' => $branch_code
        ]);
    }

    public function delete(Request $request)
    {
        DB::table('orders')
            ->where('unique_id', $request->id)
            ->delete();

        DB::table('carts')
            ->where('order_number', $request->id)
            ->delete();

        return redirect()->back()->with([
            'success' => 'Default order deleted successfully'
        ]);
    }

    public function delete_item(Request $request)
    {

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

        return redirect()->back()->with([
            'success' => 'Successfully deleted default order item'
        ]);
    }

    public function update_item(Request $request)
    {

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

        // dd('shop ', $shop->branch_code, ' products ', $product->name_english, ' price ', $price, ' cartQTY ', $qtyCart);

        Orders::where('unique_id', '=', $request->order_number)
            ->increment('total_price', $price * ($request->qty - $qtyCart));

        Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->update([
                'qty' => $request->qty,
                'remarke' => $request->remarke
            ]);

        return redirect()->back()->with([
            'success' => 'Successfully updated default order item'
        ]);
    }

    public function add_to_cart(Request $request)
    {


        // Prevent duplicate orders by checking a unique token----------------------------------------------------------------------------------------
        if (Cache::has('add_to_cart_default_token')) {
            return back()->with('error', 'Add to cart button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.');
        }
        // Store the token in the cache with a 2-second expiration
        Cache::put('add_to_cart_default_token', Str::random(40), 2); // Expires after 2 seconds
        //--------------------------------------------------------------------------------------------------------------------------------------------

        $carthas = Cart::join('shops', 'shops.branch_code', '=', 'carts.shop_bc_number')
            ->where('shops.email', '=', Auth::user()->email)
            ->whereNull('carts.order_number')
            ->whereNotNull('carts.item_number')
            ->whereNull('carts.default_name')
            ->count();
        if ($carthas) {
            return back()->with('error', 'Cart එක තුළ products එකක් තිබෙන නිසා  ඔබට මෙම default order එක cart එකට ඇතුලත් කල නොහැක.');
        }

        // $items = DB::table('carts')
        //     ->where('order_number', $request->id)
        //     ->get();

        // foreach ($items as $item) {
        //     Cart::create([
        //         'item_number' => $item->item_number,
        //         'qty' => $item->qty,
        //         'price' => $item->price,
        //         'shop_bc_number' => $item->shop_bc_number,
        //     ]);
        // }

        $items = DB::table('carts')
            ->where('order_number', $request->id)
            ->get();

        foreach ($items as $item) {
            // Check if the same item already exists in the cart
            $existingItem = Cart::where('item_number', $item->item_number)
                ->where('shop_bc_number', $item->shop_bc_number)
                ->whereNull('order_number')
                ->whereNotNull('default_name')
                ->first();

            if ($existingItem) {
                // Item exists, update the quantity or handle it as needed
                return back()->with(['error' => 'Cart already have this item']);
            }

            // Item does not exist, create a new one
            Cart::create([
                'item_number' => $item->item_number,
                'qty' => $item->qty,
                'price' => $item->price,
                'shop_bc_number' => $item->shop_bc_number,
            ]);
        }

        return back()->with(['success' => 'Successfully added.']);
    }
}

