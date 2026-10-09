<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\Shop\OrderCreated;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\Orders;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CartProcess extends Controller
{
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

        Logs::create([
            'type' => 'Update quantity',
            'message' => 'Shop updated [' . $request->item_number . '] item in the cart quantity : ' . $request->qty . ' remark : ' . $request->remarke,
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

        Logs::create([
            'type' => 'Delete item',
            'message' => 'Shop deleted [' . $request->item_number . '] item from cart',
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Successfully Deleted!')
            ->withInput();
    }

    //create order --------------------------------------------------------------------------------
    public function create_order(Request $request)
    {
        $request->validate([
            'order_time' => 'required_without:default',
        ]);

        // Prevent duplicate orders by checking a unique token----------------------------------------------------------------------------------------
        if (Cache::has('order_token')) {
            return back()->with('error', 'Order Proceed button එක, එක වතාවක් පමණක් click කර නැවත උත්සහ කරන්න.');
        }
        // Set a unique token in the session to prevent multiple submissions
        // Store the token in the cache with a 2-second expiration
        Cache::put('order_token', Str::random(40), 2); // Expires after 2 seconds
        //--------------------------------------------------------------------------------------------------------------------------------------------

        $isZero = $request->total === '0';
        if ($isZero) {
            return back()
                ->with('error', 'Order total price value must not be 0.00/=');
        }

        if ($request->filled('order_time')) {
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
        }

        $isChecked = $request->has('default') && $request->input('default') == 'on';
        if ($isChecked) {
            $uniq_id = uniqid();

            Orders::create([
                'unique_id' => $uniq_id,
                'shop' => $request->shop,
                'total_price' => $request->total,
                'note' => '',
                'time_period' => 'Default',
                'status' => 'Default',
                'default_name' => $request->note,
                'default_period' => $request->input('order_time'),
            ]);

            Cart::where('shop_bc_number', '=', $request->shop)
                ->whereNull('order_number')
                ->update([
                    'order_number' => $uniq_id,
                    'default_name' => 'Default'
                ]);

            return back()
                ->with('success', 'Successfully Created Default Order!')
                ->withInput();
        }

        $currentTime = Carbon::now();
        $startTime = Carbon::createFromTime(8, 0, 0); // 8:00 AM
        $endTime = Carbon::createFromTime(16, 15, 0); // 4:00 PM

        if ($currentTime < $startTime || $currentTime > $endTime) { // time --------------------------------------------------
            return back()
                ->with('error', 'You can create orders between 8.00 a.m. to 4.00 p.m.');
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

        Logs::create([
            'type' => 'Create Order',
            'message' => 'Shop created ' . $request->order_time . ' Order. Order unique id : ' . $uniq_id,
            'user' => Auth::user()->name,
        ]);

        $shop = Shops::where('branch_code', '=', $request->shop)
            ->first();

        $items = Cart::join('products', 'products.item_number', '=', 'carts.item_number')
            ->join('shops', 'shops.branch_code', '=', 'carts.shop_bc_number')
            ->select('products.img as img', 'products.item_number as number', 'products.name_english as name', 'carts.price as price', 'carts.qty as qty', 'carts.remarke as note')
            ->where('order_number', $uniq_id)
            ->get();

        $details = [
            'shop' => $shop,
            'email' => $shop->email,
            'contact' => $shop->contact,
            'order_id' => $uniq_id,
            'total_price' => $request->total,
            'note' => $request->note,
            'time_period' => $request->order_time,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'items' => $items
        ];

        try {
            Mail::to($shop->email)->send(new OrderCreated($details));
            return back()->with('success', 'Successfully Created Order! ');
        } catch (\Throwable $th) {
            return back()->with('success', 'Successfully Created Order! ');
        }
    }
}

