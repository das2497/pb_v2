<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrders extends Controller
{
    public function __construct(private CartService $cartService, private OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $my_shop = Shops::where('email', '=', Auth::user()->email)->first();
        $shop_bc_number = $my_shop->branch_code;

        $allowed_times = $my_shop->allowedOrderTimes();
        $period = $request->input('order_time');
        if (!in_array($period, $allowed_times, true)) {
            $period = count($allowed_times) === 1 ? $allowed_times[0] : null;
        }

        $categories = Product_category::all();

        if ($period === null) {
            return $this->respond($request, [
                'my_shop' => $shop_bc_number,
                'category' => session('category'),
                'products' => collect(),
                'cart_item' => collect(),
                'categories' => $categories,
                'period' => null,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('order_time', $period);

        $this->orderService->applyScheduledOrdersForToday($my_shop, $period);

        $category = $request->has('category') ? (string) $request->category : 'CAKE ITEMS';
        $request->session()->put('category', $category);
        $search = trim((string) $request->input('search', ''));

        $Products = Products::forPeriod($period)
            ->where('visibility', 'All')
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('item_number', 'like', "%{$search}%")
                    ->orWhere('name_english', 'like', "%{$search}%")
                    ->orWhere('name_sinhala', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->orderBy('item_number')
            ->get();

        $cart_item = DB::table('products')
            ->join('carts', 'products.item_number', '=', 'carts.item_number')
            ->when($category !== '', fn ($query) => $query->where('products.category', $category))
            ->where('carts.shop_bc_number', '=', $shop_bc_number)
            ->whereNull('order_number')
            ->select('carts.*', 'products.*')
            ->get();

        return $this->respond($request, [
            'my_shop' => $shop_bc_number,
            'category' => $category,
            'products' => $Products,
            'cart_item' => $cart_item,
            'categories' => $categories,
            'period' => $period,
            'allowed_times' => $allowed_times,
        ]);
    }

    private function respond(Request $request, array $viewData)
    {
        if ($request->ajax()) {
            return response()->json([
                'header_html' => view('shop.partials.create-order-header', $viewData)->render(),
                'order_time_options_html' => view('shop.partials.order-time-options', $viewData)->render(),
                'results_html' => view('shop.partials.create-order-results', $viewData)->render(),
            ]);
        }

        return view('shop.create-order', $viewData);
    }

    public function add_to_cart(Request $request)
    {
        $request->validate((new AddToCartRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        try {
            $this->cartService->addToCart($shop, $product, round((float) $request->qty, 2), $request->remarke);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', 'Product added to cart successfully........!');
    }

    public function addAllToCart(Request $request)
    {
        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');
        $shop = Shops::where('branch_code', '=', $branchCode)->first();

        $items = array_map(
            fn ($itemNumber) => ['item_number' => $itemNumber, 'qty' => round((float) ($quantities[$itemNumber] ?? 0), 2)],
            $selectedItems
        );

        try {
            $created = $this->cartService->addAllToCart($shop, $items, Auth::user()->name);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (empty($created)) {
            return back()->with('error', 'No items were added - select at least one item with a quantity of 1 or more.');
        }

        return back()->with('success', 'Selected items added to cart successfully!');
    }
}
