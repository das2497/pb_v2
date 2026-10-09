<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\OrderTime;
use App\Models\Product_category;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductsProcess extends Controller
{
    private function allowedOrderTimes(): array
    {
        return array_merge(OrderTime::pluck('name')->all(), ['All']);
    }

    public function add_products_view(Request $request)
    {
        if ($request->has('search')) {
            $product_categories_drop_down = Product_category::all();
            $product_categories = Product_category::paginate(10);
            $products = Products::where('name_english', 'like', '%' . $request->search . '%')
                ->orderBy('item_number', 'asc')
                ->paginate(10);

            return view('order-admin.add-products', [
                'product_categories_drop_down' => $product_categories_drop_down,
                'categories' => $product_categories,
                'products' => $products,
                'order_times' => OrderTime::orderBy('id')->get(),
            ]);
        }

        $product_categories_drop_down = Product_category::all();
        $product_categories = Product_category::paginate(10);
        $products = Products::orderBy('item_number', 'asc')->paginate(10);

        return view('order-admin.add-products', [
            'product_categories_drop_down' => $product_categories_drop_down,
            'categories' => $product_categories,
            'products' => $products,
            'order_times' => OrderTime::orderBy('id')->get(),
        ]);
    }

    public function add_product_process(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'item_number' => 'required',
            'item_name_e' => 'required',
            'item_name_s' => 'required',
            'category' => 'required',
            'visibility' => 'required',
            'order_time' => ['required', Rule::in($this->allowedOrderTimes())],
            'pb_unit_price' => 'required',
            'pb_mrp' => 'required',
            'pb_direct_sale_price' => 'required',
        ]);

        $imageName = time() . '.' . $request->file->extension();
        $request->file->move(public_path('assets/images/item-images'), $imageName);

        Products::create([
            'item_number' => $request->item_number,
            'name_english' => $request->item_name_e,
            'name_sinhala' => $request->item_name_s,
            'visibility' => $request->visibility,
            'order_time' => $request->order_time,
            'category' => $request->category,
            'unit_price' => $request->pb_unit_price,
            'mrp' => $request->pb_mrp,
            'direct_sale_price' => $request->pb_direct_sale_price,
            'img' => $imageName,
        ]);

        return redirect('/order-admin/add-products')->with('success', 'Successfully Added!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_number' => 'required|string|max:255',
            'name_english' => 'required|string|max:255',
            'name_sinhala' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'visibility' => 'required|string|max:255',
            'order_time' => ['required', Rule::in($this->allowedOrderTimes())],
            'unit_price' => 'required|numeric',
            'mrp' => 'required|numeric',
            'direct_sale_price' => 'required|numeric',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = Products::findOrFail($id);
        $product->item_number = $request->item_number;
        $product->name_english = $request->name_english;
        $product->name_sinhala = $request->name_sinhala;
        $product->category = $request->category;
        $product->visibility = $request->visibility;
        $product->order_time = $request->order_time;
        $product->unit_price = $request->unit_price;
        $product->mrp = $request->mrp;
        $product->direct_sale_price = $request->direct_sale_price;

        if ($request->hasFile('img')) {
            // Delete the old image if a new one is uploaded
            if ($product->img && file_exists(public_path('assets/images/item-images/' . $product->img))) {
                unlink(public_path('assets/images/item-images/' . $product->img));
            }
            $imageName = time() . '.' . $request->img->extension();
            $request->img->move(public_path('assets/images/item-images'), $imageName);
            $product->img = $imageName;
        }

        $product->save();

        return redirect()->back()->with('success', 'Product updated successfully ');
    }

    public function delete(Request $request, $id)
    {
        $product = Products::findOrFail($id);
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully ');
    }
}
