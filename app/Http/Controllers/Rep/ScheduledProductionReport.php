<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduledProductionReport extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('from') ? $request->input('from') : now()->toDateString();
        $to = $request->filled('to') ? $request->input('to') : now()->addDays(6)->toDateString();

        if ($to < $from) {
            [$from, $to] = [$to, $from];
        }

        $products = $this->productTotals($from, $to);
        $totals = $this->rangeTotals($from, $to);

        return view('rep.scheduled-production-report', [
            'products' => $products,
            'from' => $from,
            'to' => $to,
            'orderCount' => $totals->order_count ?? 0,
            'shopCount' => $totals->shop_count ?? 0,
        ]);
    }

    /**
     * Every product due across Scheduled orders whose order_date falls in
     * [$from, $to], summed once per product rather than per order - this is
     * the number production needs to plan around. Scoped to shops assigned
     * to the logged-in rep, same as Rep\FinalReport's route maps.
     */
    private function productTotals(string $from, string $to)
    {
        return DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', Auth::user()->email)
            ->where('orders.status', 'Scheduled')
            ->whereBetween('orders.order_date', [$from, $to])
            ->select(
                'carts.item_number',
                'products.name_english',
                'products.category',
                'products.img',
                DB::raw('SUM(carts.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT orders.unique_id) as order_count'),
                DB::raw('COUNT(DISTINCT orders.shop) as shop_count')
            )
            ->groupBy('carts.item_number', 'products.name_english', 'products.category', 'products.img')
            ->orderBy('products.category')
            ->orderBy('products.name_english')
            ->get();
    }

    /**
     * How many distinct Scheduled orders / shops (within the rep's assigned
     * shops) fall in the range, for the summary cards above the product
     * table.
     */
    private function rangeTotals(string $from, string $to)
    {
        return DB::table('orders')
            ->join('shops', 'orders.shop', '=', 'shops.branch_code')
            ->join('rep_assign_shops', 'rep_assign_shops.shop_id', '=', 'shops.id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', Auth::user()->email)
            ->where('orders.status', 'Scheduled')
            ->whereBetween('orders.order_date', [$from, $to])
            ->selectRaw('COUNT(DISTINCT orders.unique_id) as order_count, COUNT(DISTINCT orders.shop) as shop_count')
            ->first();
    }
}
