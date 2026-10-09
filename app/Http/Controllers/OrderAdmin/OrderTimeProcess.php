<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\OrderTime;
use App\Models\Products;
use Illuminate\Http\Request;

class OrderTimeProcess extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|not_in:All,all,Both,both|unique:order_times,name',
        ]);

        OrderTime::create(['name' => trim($request->name)]);

        return redirect()->back()->with('success', 'Order time added successfully');
    }

    public function delete($id)
    {
        $orderTime = OrderTime::findOrFail($id);

        if (in_array($orderTime->name, ['Morning', 'Evening'], true)) {
            return redirect()->back()->with('error', 'Morning and Evening cannot be deleted');
        }

        if (Products::where('order_time', $orderTime->name)->exists()) {
            return redirect()->back()->with('error', 'Some products still use this order time');
        }

        $orderTime->delete();

        return redirect()->back()->with('success', 'Order time deleted successfully');
    }
}
