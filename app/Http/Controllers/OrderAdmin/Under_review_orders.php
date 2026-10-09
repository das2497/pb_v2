<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\OrderTime;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Under_review_orders extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index()
    {
        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->where('status', '=', 'Under Review')
            ->select('orders.*', 'shops.*', 'orders.created_at as order_created')
            ->get();

        return view('order-admin.under-review-orders', [
            'Orders' => $Orders,
            'order_times' => OrderTime::orderBy('id')->pluck('name'),
        ]);
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->get();

        $shop = Orders::where('unique_id', '=', $request->id)->first()->shop;

        return view('order-admin.under-review-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop,
            'order_note' => $request->note
        ]);
    }

    public function delete_order(Request $request)
    {
        DB::table('carts')
            ->where('order_number', '=', $request->id)
            ->delete();

        DB::table('orders')
            ->where('unique_id', '=', $request->id)
            ->delete();

        return back()
            ->with('success', 'Order Deleted Successfully');
    }

    public function update_order(Request $request)
    {
        $today = Carbon::today();
        $datetime = Carbon::parse($today);
        $date = $datetime->format('Y-m-d'); // Result: "2024-08-27"

        // dd($request->shop, ' ', $request->period, ' ', $date);

        $excistingorder = Orders::where('shop', '=', $request->shop)
            ->where('time_period', '=', $request->period)
            ->whereDate('created_at', $date)
            ->first();

        if ($excistingorder && $excistingorder->time_period == $request->period && $request->order_cancel == 'on') {
            $this->orderService->transitionToCancelled($request->order_id);
            return back()->with('success', 'Success!');
        }

        if ($excistingorder && $excistingorder->count() > 0) {
            return back()
                ->with('error', 'Order already created for this time period in this shop.');
        }

        $status = $request->state;

        if ($request->order_cancel == 'on') {
            $this->orderService->transitionToCancelled($request->order_id);

            return back()
                ->with('success', 'Order successfully cancelled');
        }

        if (Orders::where('unique_id', '=', $request->order_id)->value('time_period') !== $request->period) {
            $orderShop = Shops::where('branch_code', Orders::where('unique_id', $request->order_id)->value('shop'))->first();
            if (!$orderShop || !in_array($request->period, $orderShop->allowedOrderTimes(), true)) {
                return back()->with('error', 'This order time is not allowed for this shop.');
            }

            $incompatible = Products::incompatibleItemNames($request->order_id, $request->period);
            if ($incompatible->isNotEmpty()) {
                return back()->with('error', 'These items are not available for ' . $request->period . ' orders: ' . $incompatible->implode(', '));
            }
        }

        Orders::where('unique_id', '=', $request->order_id)
            ->update([
                'time_period' => $request->period,
                'status' => $status,
            ]);

        return back()
            ->with('success', 'Updated successfully');
    }

    public function update(Request $request)
    {
        $request->validate([
            'qty' => 'required',
        ]);

        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = $product->priceFor($shop);

        $qtyCart = Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->first()->qty;

        Orders::where('unique_id', '=', $request->order_number)
            ->increment('total_price', $price * ($request->qty - $qtyCart));

        Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->update([
                'qty' => $request->qty,
                'remarke' => $request->remarke
            ]);

        return back()
            ->with('success', 'Quantity Updated Successfully');
    }

    public function delete(Request $request)
    {

        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = $product->priceFor($shop);

        Orders::where('unique_id', '=', $request->order_number)
            ->decrement('total_price', $price * $request->qty);

        Cart::where('order_number', '=', $request->order_number)
            ->where('item_number', '=', $request->item_number)
            ->delete();

        return back()
            ->with('success', 'Successfully deleted!');
    }
}

