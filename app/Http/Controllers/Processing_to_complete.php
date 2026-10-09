<?php

namespace App\Http\Controllers;

use App\Services\OrderService;

class Processing_to_complete extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index()
    {
        $this->orderService->transferProcessingToComplete();

        return back()->with('success', 'Successfuly transferred processing orders to complete orders');
    }
}

