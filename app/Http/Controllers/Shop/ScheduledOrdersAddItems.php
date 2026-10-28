<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduledOrdersAddItems extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        $categories = Product_category::all();
        $order = Orders::where('unique_id', '=', $request->order_number)->firstOrFail();
        $period = $order->time_period;

        $category = $request->has('category') ? (string) $request->category : 'CAKE ITEMS';
        $request->session()->put('category', $category);
        $search = trim((string) $request->input('search', ''));

        $products = Products::forPeriod($period)
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('item_number', 'like', "%{$search}%")
                    ->orWhere('name_english', 'like', "%{$search}%");
            }))
            ->orderBy('item_number')
            ->get();

        $cart_items = DB::table('carts')
            ->join('products', 'carts.item_number', '=', 'products.item_number')
            ->where('carts.order_number', '=', $request->order_number)
            ->select('products.*', 'carts.*')
            ->get();

        $viewData = [
            'category' => $category,
            'search' => $search,
            'products' => $products,
            'cart_items' => $cart_items,
            'order' => $order,
            'order_number' => $request->order_number,
            'categories' => $categories,
        ];

        if ($request->ajax()) {
            return response()->json([
                'header_html' => view('shop.partials.scheduled-order-add-items-header', $viewData)->render(),
                'results_html' => view('shop.partials.scheduled-order-add-items-results', $viewData)->render(),
            ]);
        }

        return view('shop.scheduled-orders-add-items', $viewData);
    }

    public function store(Request $request)
    {
        $orderNumber = $request->input('order_number');
        $order = Orders::where('unique_id', '=', $orderNumber)->firstOrFail();
        $shop = Shops::where('branch_code', '=', $order->shop)->first();

        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);

        $added = 0;

        foreach ($selectedItems as $itemNumber) {
            $qty = round((float) ($quantities[$itemNumber] ?? 0), 2);
            if ($qty <= 0) {
                continue;
            }

            if (!Products::availableForOrder($itemNumber, $orderNumber)) {
                continue;
            }

            $product = Products::where('item_number', '=', $itemNumber)->first();
            if (!$product) {
                continue;
            }

            $price = $product->priceFor($shop);
            $this->cartService->addItemToExistingOrder($orderNumber, $itemNumber, $order->shop, $price, $qty);
            $added++;
        }

        if ($added === 0) {
            return back()->with('error', 'No items were added - select at least one item with a quantity of 1 or more.');
        }

        return back()->with('success', 'Products added to scheduled order successfully!');
    }
}
