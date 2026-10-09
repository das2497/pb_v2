<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'branch_code' => 'required|string|max:255',
            'email' => 'required|email',
            'price_range' => 'required|string|max:255',
            'morning_route' => 'required|string|max:255',
            'evening_route' => 'required|string|max:255',
            'order_time' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ];
    }
}
