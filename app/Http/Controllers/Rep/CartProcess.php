<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Rep;
use App\Models\Shops;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartProcess extends Controller
{
    public function index(Request $request)
    {
        $branch_code = $request->query('shop');

        // dd($branch_code);

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
        $request->validate([
            'qty' => 'required',
        ]);

        Cart::where('item_number', '=', $request->item_number)
            ->where('shop_bc_number', '=', $request->branch_code)
            ->whereNull('order_number')
            ->update([
                'qty' => $request->qty,
                'remarke' => $request->remarke
            ]);

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
        Cart::where('item_number', '=', $request->item_number)
            ->where('shop_bc_number', '=', $request->branch_code)
            ->whereNull('order_number')
            ->delete();

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

        $cart = Cart::where('shop_bc_number', '=', $request->shop)
            ->whereNull('order_number')
            ->get();

        if ($cart->count() == 0) {
            return back()->with('error', 'Cart is empty');
        }

        $request->validate([
            'order_time' => 'required',
        ]);

        $currentTime = Carbon::now();

        $startTime = Carbon::createFromTime(8, 0, 0); // 8:00 AM
        $endTime = Carbon::createFromTime(16, 0, 0); // 4:00 PM

        // if ($currentTime->lt($startTime) || $currentTime->gt($endTime)) {
        //     return back()
        //         ->with('error', 'You can create orders between 8.00 a.m. to 4.00 p.m.');
        // }

        $rep = Rep::where('email', '=', Auth::user()->email)->first();

        if ($rep->access == 'off') {
            return back()->with('error', 'You do not have access to create orders.');
        }

        $orderShop = Shops::where('branch_code', $request->shop)->firstOrFail();
        if (!in_array($request->order_time, $orderShop->allowedOrderTimes(), true)) {
            return back()->with('error', 'Invalid order time for this shop.');
        }

        $mismatched = Cart::join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.shop_bc_number', $request->shop)
            ->whereNull('carts.order_number')
            ->whereNotIn('products.order_time', [$request->order_time, 'All'])
            ->pluck('products.name_english');
        if ($mismatched->isNotEmpty()) {
            return back()->with('error', 'These items are not available for ' . $request->order_time . ' orders, please remove them from the cart: ' . $mismatched->implode(', '));
        }

        $today = Carbon::today();
        $datetime = Carbon::parse($today);
        $date = $datetime->format('Y-m-d'); // Result: "2024-08-27"
        $excistingorder = Orders::whereDate('created_at', $date)
            ->where('time_period', $request->order_time)
            ->where('shop', $request->shop)
            ->get();
        if ($excistingorder->count() > 0) { // order repeat -----------------------------------------------------------------------------------------
            return back()
                ->with('error', 'Order already created for this time period in this shop.');
        }

        $uniq_id = uniqid();

        Orders::create([
            'unique_id' => $uniq_id,
            'shop' => $request->shop,
            'total_price' => $request->total,
            'note' => $request->note,
            'time_period' => $request->order_time,
            'status' => 'Pending'
        ]);

        Cart::where('shop_bc_number', '=', $request->shop)
            ->whereNull('order_number')
            ->update(['order_number' => $uniq_id]);

        $shop_name = Shops::where('branch_code', '=', $request->shop)->first()->name;

        Logs::create([
            'type' => 'Create Order',
            'message' => 'Rep created [' . $request->shop . ']' . $shop_name . '\'s ' . $request->order_time . ' Order. Order unique id : ' . $uniq_id,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Successfully Created Order!');
    }
}
