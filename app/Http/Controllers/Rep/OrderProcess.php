<?php

namespace App\Http\Controllers\Rep;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\VerifyFormTokenRequest;
use App\Models\Cart;
use App\Models\Logs;
use App\Models\OrderTime;
use App\Models\Product_category;
use App\Models\Products;
use App\Models\Shops;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderProcess extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        $Shops = DB::table('rep_assign_shops')
            ->join('shops', 'shops.id', '=', 'rep_assign_shops.shop_id')
            ->join('reps', 'reps.id', '=', 'rep_assign_shops.rep_id')
            ->where('reps.email', '=', Auth::user()->email)
            ->select('shops.*')
            ->get();

        $categories = Product_category::all();

        $form_token = Str::random(40);
        session()->put('form_token', $form_token);

        $my_shop = $request->input('shop');
        $selectedShop = $Shops->contains('branch_code', $my_shop)
            ? Shops::where('branch_code', $my_shop)->first()
            : null;
        $allowed_times = $selectedShop
            ? $selectedShop->allowedOrderTimes()
            : OrderTime::orderBy('id')->pluck('name')->all();
        $period = $request->input('order_time');
        if (!$selectedShop || !in_array($period, $allowed_times, true)) {
            $period = ($selectedShop && count($allowed_times) === 1) ? $allowed_times[0] : null;
        }

        if (!$selectedShop || $period === null) {
            return $this->respond($request, [
                'category' => $request->input('category', session('category', '')),
                'search' => (string) $request->input('search', ''),
                'shops' => $Shops,
                'selected_shop' => $selectedShop,
                'products' => collect(),
                'cart_item' => collect(),
                'categories' => $categories,
                'my_shop' => $my_shop,
                'form_token' => $form_token,
                'period' => null,
                'allowed_times' => $allowed_times,
            ]);
        }

        $request->session()->put('order_time', $period);

        $category = $request->has('category') ? (string) $request->category : 'CAKE ITEMS';
        $request->session()->put('category', $category);
        $search = trim((string) $request->input('search', ''));

        $Products = Products::forPeriod($period)
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('item_number', 'like', "%{$search}%")
                    ->orWhere('name_english', 'like', "%{$search}%")
                    ->orWhere('name_sinhala', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            }))
            ->orderBy('item_number', 'asc')
            ->get();

        $cart_item = DB::table('products')
            ->join('carts', 'products.item_number', '=', 'carts.item_number')
            ->when($category !== '', fn ($query) => $query->where('products.category', $category))
            ->where('carts.shop_bc_number', '=', $my_shop)
            ->whereNull('order_number')
            ->select('carts.*', 'products.*')
            ->get();

        return $this->respond($request, [
            'category' => $category,
            'search' => $search,
            'shops' => $Shops,
            'selected_shop' => $selectedShop,
            'products' => $Products,
            'cart_item' => $cart_item,
            'categories' => $categories,
            'my_shop' => $my_shop,
            'form_token' => $form_token,
            'period' => $period,
            'allowed_times' => $allowed_times,
        ]);
    }

    private function respond(Request $request, array $viewData)
    {
        if ($request->ajax()) {
            return response()->json([
                'header_html' => view('rep.partials.create-order-header', $viewData)->render(),
                'order_time_options_html' => view('rep.partials.order-time-options', $viewData)->render(),
                'results_html' => view('rep.partials.create-order-results', $viewData)->render(),
            ]);
        }

        return view('rep.create-order', $viewData);
    }

    public function add_to_cart(Request $request)
    {
        $request->validate((new AddToCartRequest())->rules());

        $shop = Shops::where('branch_code', '=', $request->branch_code)->first();
        $product = Products::where('item_number', '=', $request->item_number)->first();

        try {
            $this->cartService->addItemToCart($shop, $product, round((float) $request->qty, 2));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()
            ->with('success', 'Product added to cart successfully!');
    }

    public function addAllToCart(Request $request)
    {
        $request->validate((new VerifyFormTokenRequest())->rules());

        if ($request->form_token !== session('form_token')) {
            return redirect()->back()->withErrors(['error' => 'Duplicate submission detected.']);
        }

        session()->forget('form_token');

        $selectedItems = $request->input('selected_items', []);
        $quantities = $request->input('qty', []);
        $branchCode = $request->input('branch_code');
        $shop = Shops::where('branch_code', '=', $branchCode)->first();

        $items = array_map(
            fn ($itemNumber) => ['item_number' => $itemNumber, 'qty' => round((float) ($quantities[$itemNumber] ?? 0), 2)],
            $selectedItems
        );

        try {
            $this->cartService->addAllItemsToCart($shop, $items);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $shop_name = $shop->name;

        Logs::create([
            'type' => 'Add To Cart',
            'message' => 'Rep add to cart' . implode(' ', $selectedItems) . ' items to [' . $branchCode . ']' . $shop_name,
            'user' => Auth::user()->name,
        ]);

        return back()->with('success', 'Selected items added to cart successfully!');
    }

    public function clearcart(Request $request)
    {

        Cart::where('shop_bc_number', $request->shop)
            ->whereNull('order_number')
            ->whereNull('default_name')
            ->delete();

        return back()->with('success', 'Successfully cleared!');
    }
}
