<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderTimeRequest;
use App\Models\OrderTime;
use App\Models\Products;
use Illuminate\Http\Request;

class OrderTimeProcess extends Controller
{
    public function store(Request $request)
    {
        $request->validate((new StoreOrderTimeRequest())->rules());

        OrderTime::create(['name' => trim($request->name)]);

        return redirect()->back()->with('success', 'Order time added successfully');
    }

    public function delete($id)
    {
        $orderTime = OrderTime::findOrFail($id);

        if (in_array($orderTime->name, ['Morning', 'Evening'], true)) {
            return redirect()->back()->with('error', 'Morning and Evening cannot be deleted');
        }

        $name = $orderTime->name;
        $stillInUse = Products::where(function ($q) use ($name) {
            $q->where('order_time', $name)
                ->orWhere('order_time', 'like', $name . ',%')
                ->orWhere('order_time', 'like', '%,' . $name)
                ->orWhere('order_time', 'like', '%,' . $name . ',%');
        })->exists();

        if ($stillInUse) {
            return redirect()->back()->with('error', 'Some products still use this order time');
        }

        $orderTime->delete();

        return redirect()->back()->with('success', 'Order time deleted successfully');
    }
}
