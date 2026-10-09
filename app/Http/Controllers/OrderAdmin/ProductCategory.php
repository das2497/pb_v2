<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product_category;
use Illuminate\Http\Request;

class ProductCategory extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'main_category' => 'required',
            'category' => 'required',
        ]);

        Product_category::create([
            'category' => $request->category,
            'main_category' => $request->main_category,
        ]);

        return redirect()->back()->with('success', 'Product inserted successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'main_category' => 'required',
            'category' => 'required',
        ]);

        $product_category = Product_category::findOrFail($id);
        $product_category->category = $request->category;
        $product_category->main_category = $request->main_category;

        $product_category->save();

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function delete(Request $request, $id){
        $product_category = Product_category::findOrFail($id);
        $product_category->delete();
        return redirect()->back()->with('success', 'Product deleted successfully ');
    }
}
