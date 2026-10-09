<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\OrderTime;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Processing_orders extends Controller
{
    public function index(Request $request)
    {
        $Orders = null;

        if ($request->has('search') && $request->search != '') {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop') // INNER JOIN
                ->where('orders.status', '=', 'Processing') // Filter by status
                ->where(function ($query) use ($request) { // Group OR conditions
                    $query->Where('orders.unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('shops.name', 'like', '%' . $request->search . '%')
                        ->orWhere('orders.time_period', 'like', '%' . $request->search . '%');
                })
                ->orderBy('orders.created_at', 'desc') // Order by created_at column
                ->select('orders.*', 'shops.*', 'orders.created_at as order_created') // Select columns with alias
                ->get();
        } else {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                ->where('orders.status', '=', 'Processing')
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.*', 'shops.*', 'orders.created_at as order_created')
                ->get();
        }

        return view('order-admin.processing-orders', [
            'Orders' => $Orders,
            'order_times' => OrderTime::orderBy('id')->pluck('name'),
        ]);
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->join('product_categories', 'product_categories.category', '=', 'products.category')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

        $shop = Orders::where('unique_id', '=', $request->id)->first()->shop;

        $shop_name = Shops::where('branch_code', '=', $shop)->first()->name;

        return view('order-admin.processing-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop,
            'shop_name' => $shop_name,
            'order_note' => $request->note
        ]);
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
            Orders::where('unique_id', '=', $request->order_id)
                ->update([
                    'time_period' => 'Cancelled',
                    'status' => 'Cancelled',
                    'note' => $request->note
                ]);
            return back()->with('success', 'Success!');
        }

        if ($excistingorder && $excistingorder->count() > 0) {
            return back()
                ->with('error', 'Order already created for this time period in this shop.');
        }

        $status = $request->state;

        if ($request->order_cancel == 'on') {
            $status = 'Cancelled';

            Orders::where('unique_id', '=', $request->order_id)
                ->update([
                    'time_period' => $status,
                    'status' => $status,
                    'note' => $request->note
                ]);

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
                'note' => $request->note
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

