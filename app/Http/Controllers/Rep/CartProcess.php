<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
use App\Models\Logs;
use App\Models\Rep;
use App\Models\Shops;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return view('rep.cart', [
            'shop' => $shop,
            'carts' => $cart,
            'branch_code' => $branch_code,
            'period' => $period,
        ]);
    }

    public function update_qty(Request $request)
    {
        $request->validate((new UpdateOrderItemQuantityRequest())->rules());

        $this->cartService->updateCartItemQty($request->item_number, $request->branch_code, round((float) $request->qty, 2), $request->remarke);

        $shop_name = Shops::where('branch_code', '=', $request->branch_code)->first()->name;

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Rep updated cart item [' . $request->item_number . '] to quantity [' . $request->qty . '] and remark [' . $request->remarke . '] in [' . $request->branch_code . ']' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Quantity updated successfully')
            ->withInput();
    }

    public function delete_item(Request $request)
    {
        $this->cartService->deleteCartItem($request->item_number, $request->branch_code);

        $shop_name = Shops::where('branch_code', '=', $request->branch_code)->first()->name;

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Rep deleted cart item [' . $request->item_number . '] in [' . $request->branch_code . ']' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Successfully Deleted!')
            ->withInput();
    }

    public function create_order(Request $request)
    {
        $shop = Shops::where('branch_code', '=', $request->shop)->firstOrFail();

        $cartIsEmpty = !DB::table('carts')->where('shop_bc_number', '=', $request->shop)->whereNull('order_number')->exists();
        if ($cartIsEmpty) {
            return back()->with('error', 'Cart is empty');
        }

        $request->validate((new CreateOrderRequest())->rules());

        // Rep's time-window check is intentionally disabled (commented out
        // in the pre-Phase-3 version) — reps can create orders any time,
        // unlike shops. Not a gap to "fix"; this is the real current rule.

        $rep = Rep::where('email', '=', Auth::user()->email)->first();
        if ($rep->access == 'off') {
            return back()->with('error', 'You do not have access to create orders.');
        }

        try {
            $this->orderService->assertPeriodAllowed($shop, $request->order_time);
            $this->orderService->assertNoMismatchedItems($shop, $request->order_time);
            $this->orderService->assertNoDuplicateForToday($shop, $request->order_time);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $order = $this->orderService->persistPendingOrder($shop, $request->order_time, (float) $request->total, $request->note);

        Logs::create([
            'type' => 'Create Order',
            'message' => 'Rep created [' . $request->shop . ']' . $shop->name . '\'s ' . $request->order_time . ' Order. Order unique id : ' . $order->unique_id,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Successfully Created Order!');
    }
}
