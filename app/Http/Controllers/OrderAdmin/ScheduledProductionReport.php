<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        $selectedTimePeriods = $this->normalizeArray($request->input('time_periods', []));

        $products = $this->productTotals($from, $to, $selectedTimePeriods);
        $totals = $this->rangeTotals($from, $to, $selectedTimePeriods);

        return view('order-admin.scheduled-production-report', [
            'products' => $products,
            'from' => $from,
            'to' => $to,
            'orderCount' => $totals->order_count ?? 0,
            'shopCount' => $totals->shop_count ?? 0,
            'orderTimes' => DB::table('order_times')->orderBy('id')->pluck('name'),
            'selectedTimePeriods' => $selectedTimePeriods,
        ]);
    }

    /**
     * Every product due across Scheduled orders whose order_date falls in
     * [$from, $to] (and whose time_period is in $timePeriods, when given),
     * summed once per product rather than per order - this is the number
     * production needs to plan around.
     */
    private function productTotals(string $from, string $to, array $timePeriods)
    {
        $query = DB::table('orders')
            ->join('carts', 'orders.unique_id', '=', 'carts.order_number')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('orders.status', 'Scheduled')
            ->whereBetween('orders.order_date', [$from, $to]);

        if (!empty($timePeriods)) {
            $query->whereIn('orders.time_period', $timePeriods);
        }

        return $query
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
     * How many distinct Scheduled orders / shops fall in the range (and
     * match the selected time periods, when given), for the summary cards
     * above the product table.
     */
    private function rangeTotals(string $from, string $to, array $timePeriods)
    {
        $query = DB::table('orders')
            ->where('status', 'Scheduled')
            ->whereBetween('order_date', [$from, $to]);

        if (!empty($timePeriods)) {
            $query->whereIn('time_period', $timePeriods);
        }

        return $query
            ->selectRaw('COUNT(DISTINCT unique_id) as order_count, COUNT(DISTINCT shop) as shop_count')
            ->first();
    }

    private function normalizeArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return $value ? [$value] : [];
    }
}
