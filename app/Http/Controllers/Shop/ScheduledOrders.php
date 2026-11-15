<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderItemQuantityRequest;
use App\Models\OrderTime;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduledOrders extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private CartService $cartService,
    ) {
    }

    public function index(Request $request)
    {
        $shop = Shops::where('email', '=', Auth::user()->email)->first();

        $sort = in_array($request->query('sort'), ['order_date', 'order_time'], true)
            ? $request->query('sort')
            : 'order_date';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $query = Orders::where('status', '=', 'Scheduled')
            ->where('shop', '=', $shop->branch_code);

        if ($sort === 'order_time') {
            $this->orderByTimePeriod($query, $direction);
            $query->orderBy('order_date');
        } else {
            $query->orderBy('order_date', $direction);
            $this->orderByTimePeriod($query, 'asc');
        }

        return view('shop.scheduled-orders', [
            'orders' => $query->get(),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    /**
     * Orders by time period chronologically (Morning before Evening)
     * rather than alphabetically, using the order_times table's own id
     * sequence as the chronological order.
     */
    private function orderByTimePeriod($query, string $direction): void
    {
        $periods = OrderTime::orderBy('id')->pluck('name')->all();

        if ($periods === []) {
            $query->orderBy('time_period', $direction);

            return;
        }

        $placeholders = implode(',', array_fill(0, count($periods), '?'));
        $query->orderByRaw("FIELD(time_period, {$placeholders}) {$direction}", $periods);
    }

    public function create()
    {
        $shop = Shops::where('email', '=', Auth::user()->email)->first();

        return view('shop.scheduled-orders-create', [
            'allowed_times' => $shop->allowedOrderTimes(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_date' => 'required|date|after:' . Carbon::today()->toDateString(),
            'order_time' => 'required|string',
        ], [
            'order_date.after' => 'Scheduled order එකක් හෙට හෝ ඉන් පසු දිනයකට පමණක් සෑදිය හැක. අද දිනයට හෝ ඊට පෙර දිනයකට schedule කළ නොහැක.',
        ]);

        $shop = Shops::where('email', '=', Auth::user()->email)->first();

        try {
            $order = $this->orderService->createScheduledOrder($shop, $request->order_time, $request->order_date);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('shop.scheduled-orders-add-items', ['order_number' => $order->unique_id])
            ->with('success', 'Scheduled order created, now add products to it.');
    }

    public function view(Request $request)
    {
        $order = Orders::where('unique_id', '=', $request->id)
            ->where('status', '=', 'Scheduled')
            ->firstOrFail();

        $items = DB::table('carts')
            ->join('products', 'products.item_number', '=', 'carts.item_number')
            ->where('carts.order_number', '=', $request->id)
            ->orderBy('products.item_number')
            ->get();

        return view('shop.scheduled-orders-view', [
            'items' => $items,
            'order' => $order,
        ]);
    }

    public function delete(Request $request)
    {
        $this->orderService->deleteOrder($request->id);

        return redirect()->back()->with([
            'success' => 'Scheduled order deleted successfully'
        ]);
    }

    public function delete_item(Request $request)
    {
        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = $product->priceFor($shop);

        $this->cartService->removeOrderItem($request->order_number, $request->item_number, $price, $request->qty);

        return redirect()->back()->with([
            'success' => 'Successfully deleted scheduled order item'
        ]);
    }

    public function update_item(Request $request)
    {
        $request->validate((new UpdateOrderItemQuantityRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->shop)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        $price = $product->priceFor($shop);

        $this->cartService->applyOrderItemQtyChange($request->order_number, $request->item_number, $price, round((float) $request->qty, 2), $request->remarke);

        return redirect()->back()->with([
            'success' => 'Successfully updated scheduled order item'
        ]);
    }
}
