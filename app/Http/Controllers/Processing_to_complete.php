<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;

class Processing_to_complete extends Controller
{
    public function index()
    {
        Orders::where('status', '=', 'Processing')
            ->update(['status' => 'Complete']);

        return back()->with('success', 'Successfuly transferred processing orders to complete orders');
    }
}

