<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'name_sinhala' => 'required',
            'branch_code' => 'required',
            'email' => 'required',
            'contact' => 'required',
            'price_range' => 'required',
            'morning_route' => 'required',
            'evening_route' => 'required',
            // Optional: only shops that actually go out on a beverage route
            // get one, and only those appear in the Beverage shop report.
            'beverage_route' => 'nullable|string|max:255',
            'type' => 'required',
            'order_time' => 'required',
            'password' => 'required',
        ];
    }
}
