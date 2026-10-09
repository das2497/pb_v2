<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShopOrderRequest;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
use App\Mail\Shop\OrderCreated;
use App\Models\Cart;
use App\Models\Shops;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CartProcess extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
    ) {
    }

    public function index(Request $request)
    {
        $branch_code = $request->query('shop');

        $cart = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->join('shops', 'shops.branch_code', '=', 'carts.shop_bc_number')
            ->select('carts.*', 'products.*', 'shops.*')
            ->where('carts.shop_bc_number', $branch_code)
            ->whereNull('order_number')
            ->orderBy('products.item_number')
            ->get();

        $shop = Shops::where('branch_code', '=', $branch_code)
            ->first();

        $allowed_times = $shop->allowedOrderTimes();
        $period = session('order_time');
        if (!in_array($period, $allowed_times, true)) {
            $period = count($allowed_times) === 1 ? $allowed_times[0] : null;
        }

        return view('shop.cart', [
            'shop' => $shop,
            'carts' => $cart,
            'period' => $period,
        ]);
    }

    public function update_qty(Request $request)
    {
        $request->validate((new UpdateOrderItemQuantityRequest())->rules());

        $this->cartService->updateQty($request->item_number, $request->branch_code, (int) $request->qty, $request->remarke, Auth::user()->name);

        return back()
            ->with('success', 'Quantity updated successfully')
            ->withInput();
    }

    public function delete_item(Request $request)
    {
        $this->cartService->deleteItem($request->item_number, $request->branch_code, Auth::user()->name);

        return back()
            ->with('success', 'Successfully Deleted!')
            ->withInput();
    }

    public function create_order(Request $request)
    {
        $request->validate((new CreateShopOrderRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->shop)->firstOrFail();
        $isDefault = $request->has('default') && $request->input('default') == 'on';

        try {
            if ($isDefault) {
                $this->orderService->createDefaultOrder($shop, $request->input('order_time'), (float) $request->total, $request->note, Auth::user()->name);

                return back()
                    ->with('success', 'Successfully Created Default Order!')
                    ->withInput();
            }

            $order = $this->orderService->createOrder($shop, $request->order_time, (float) $request->total, $request->note, Auth::user()->name);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $items = Cart::join('products', 'products.item_number', '=', 'carts.item_number')
            ->join('shops', 'shops.branch_code', '=', 'carts.shop_bc_number')
            ->select('products.img as img', 'products.item_number as number', 'products.name_english as name', 'carts.price as price', 'carts.qty as qty', 'carts.remarke as note')
            ->where('order_number', $order->unique_id)
            ->get();

        $details = [
            'shop' => $shop,
            'email' => $shop->email,
            'contact' => $shop->contact,
            'order_id' => $order->unique_id,
            'total_price' => $request->total,
            'note' => $request->note,
            'time_period' => $request->order_time,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'items' => $items,
        ];

        try {
            Mail::to($shop->email)->send(new OrderCreated($details));
        } catch (\Throwable $th) {
            // Mail failure is intentionally swallowed here, matching
            // existing behavior — the order itself is already created.
        }

        return back()->with('success', 'Successfully Created Order! ');
    }
}
