<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
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

        $order = Orders::where('unique_id', '=', $request->order_number)
            ->select('time_period', 'shop')
            ->first();

        $category = $request->has('category') ? (string) $request->input('category') : 'CAKE ITEMS';
        $request->session()->put('category', $category);

        $search = trim((string) $request->input('search'));

        $cart_item = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number')
            ->where('carts.order_number', '=', $request->order_number)
            ->select('products.*', 'carts.*')
            ->get();

        $products = Products::forPeriod($order->time_period)
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', '=', $category);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('item_number', 'like', '%' . $search . '%')
                        ->orWhere('name_english', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('item_number')
            ->get();

        return view('rep.pending-orders-add-items', [
            'category' => $category,
            'products' => $products,
            'cart_items' => $cart_item,
            'order_number' => $request->order_number,
            'shop' => $order->shop,
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
            'message' => 'Rep add item ' . $request->item_number . ' quantity ' . $request->qty . ' to pending order ' . $request->order_number . ' to ' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Product added to pending order successfully!');
    }
}
