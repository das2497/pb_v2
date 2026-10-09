<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\OrderTime;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pending_orders extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index(Request $request)
    {

        $Orders = null;

        if ($request->has('search') && $request->search != '') {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop') // INNER JOIN
                ->where('orders.status', '=', 'Pending') // Filter by status
                ->where(function ($query) use ($request) { // Group OR conditions
                    $query->where('orders.unique_id', 'like', '%' . $request->search . '%')
                        ->orWhere('shops.name', 'like', '%' . $request->search . '%')
                        ->orWhere('orders.time_period', 'like', '%' . $request->search . '%');
                })
                ->select('orders.*', 'shops.*', 'orders.created_at as order_created') // Select columns with alias
                ->get();
        } else {
            $Orders = DB::table('orders')
                ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                ->where('orders.status', '=', 'Pending')
                ->select('orders.*', 'shops.*', 'orders.created_at as order_created')
                ->get();
        }


        return view('order-admin.pending-orders', [
            'Orders' => $Orders,
            'order_times' => OrderTime::orderBy('id')->pluck('name'),
        ]);
    }

    public function note_update(Request $request)
    {
        Orders::where('unique_id', '=', $request->order_id)
            ->update(['note' => $request->note]);

        Logs::create([
            'type' => 'Update Pending Order Note',
            'message' => 'Order admin updated note in pending order ' . $request->order_id,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Note updated successfully');
    }

    public function view(Request $request)
    {
        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->select('carts.*', 'products.*')
            ->orderBy('products.item_number')
            ->get();

        $shop = Orders::where('unique_id', '=', $request->id)->first()->shop;

        return view('order-admin.pending-orders-view', [
            'items' => $items,
            'order_number' => $request->id,
            'shop' => $shop,
            'order_note' => $request->note
        ]);
    }

    public function update_order(Request $request)
    {
        $date = Carbon::today()->format('Y-m-d');

        // Check if order exists for the same shop, period, and date
        $existingOrder = Orders::where('shop', $request->shop)
            ->where('time_period', $request->period)
            ->whereDate('created_at', $date)
            ->first();

        // If order exists and it's a cancellation
        if ($existingOrder && $request->order_cancel == 'on') {
            $this->orderService->transitionToCancelled($request->order_id);
            Orders::where('unique_id', $request->order_id)->update(['note' => $request->note]);

            Logs::create([
                'type' => 'Order update',
                'message' => 'Order admin canceled pending order : ' . $request->order_number,
                'user' => Auth::user()->name,
            ]);

            return back()->with('success', 'Successfully cancelled order!');
        }

        // If order exists and is not being cancelled
        if ($existingOrder) {
            return back()->with('error', 'Order already created for this time period in this shop.');
        }

        if (Orders::where('unique_id', $request->order_id)->value('time_period') !== $request->period) {
            $orderShop = Shops::where('branch_code', Orders::where('unique_id', $request->order_id)->value('shop'))->first();
            if (!$orderShop || !in_array($request->period, $orderShop->allowedOrderTimes(), true)) {
                return back()->with('error', 'This order time is not allowed for this shop.');
            }

            $incompatible = Products::incompatibleItemNames($request->order_id, $request->period);
            if ($incompatible->isNotEmpty()) {
                return back()->with('error', 'These items are not available for ' . $request->period . ' orders: ' . $incompatible->implode(', '));
            }
        }

        Orders::where('unique_id', $request->order_id)
            ->update([
                'time_period' => $request->period,
                'note' => $request->note
            ]);

        Logs::create([
            'type' => 'Order update',
            'message' => 'Order admin updated pending order time period : [' . $request->period . '] in order : ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Updated successfully');
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

        Logs::create([
            'type' => 'Update item pending Order',
            'message' => 'Order admin updated item : [' . $request->item_number . '] quantity : [' . $request->qty . '] remark : [' . $request->remarke . '] in pending order ' . $request->order_number,
            'user' => Auth::user()->name,
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

        Logs::create([
            'type' => 'Delete item pending Order',
            'message' => 'Order admin deleted item : [' . $request->item_number . '] from pending order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return back()
            ->with('success', 'Successfully deleted!');
    }

    public function accept(Request $request)
    {
        $this->orderService->transitionToProcessing($request->order_number);

        Logs::create([
            'type' => 'Accept Pending Order',
            'message' => 'Order admin accept pending order ' . $request->order_number,
            'user' => Auth::user()->name,
        ]);

        return redirect('/order-admin/pending-orders')
            ->with('success', 'Order Accepted Successfully');
    }
}
