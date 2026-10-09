<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompleteOrders extends Controller
{
    public function index(Request $request)
    {
        $Orders = null;

        if ($request->has('search') && $request->search != '') {
            $request->validate([
                'date' => 'required'
            ]);

            if ($request->has('date') && $request->date == null) {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                    ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                    ->where(function ($query) use ($request) {
                        $query->where('orders.unique_id', 'like', '%' . $request->search . '%')
                            ->orWhere('shops.name', 'like', '%' . $request->search . '%');
                    })
                    ->where('status', '=', 'Complete')
                    ->where('reps.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            } else {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                    ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                    ->whereDate('orders.created_at', '=', $request->date)
                    ->where(function ($query) use ($request) {
                        $query->where('orders.unique_id', 'like', '%' . $request->search . '%')
                            ->orWhere('shops.name', 'like', '%' . $request->search . '%');
                    })
                    ->where('status', '=', 'Complete')
                    ->where('reps.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            }

            return view('rep.complete-order', [
                'Orders' => $Orders
            ]);
        }

        if ($request->has('search') && $request->search == '') {


            if ($request->has('date') && $request->date == null) {
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                    ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                    ->where('status', '=', 'Complete')
                    ->where('reps.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            } else {
                $request->validate([
                    'date' => 'required'
                ]);
                $Orders = DB::table('orders')
                    ->join('shops', 'shops.branch_code', '=', 'orders.shop')
                    ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
                    ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
                    ->whereDate('orders.created_at', '=', $request->date)
                    ->where('status', '=', 'Complete')
                    ->where('reps.email', '=', Auth::user()->email)
                    ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
                    ->orderBy('orders.created_at', 'desc')
                    ->paginate(20);
            }

            return view('rep.complete-order', [
                'Orders' => $Orders
            ]);
        }

        $Orders = DB::table('orders')
            ->join('shops', 'shops.branch_code', '=', 'orders.shop')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('status', '=', 'Complete')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('orders.*', 'shops.*', 'orders.created_at as order_time')
            ->orderBy('orders.created_at', 'desc')
            ->paginate(20);

        return view('rep.complete-order', [
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

        return view('rep.complete-orders-view', [
            'items' => $items,
            'order_number' => $request->id
        ]);
    }
}
