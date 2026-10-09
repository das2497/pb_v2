<?php

namespace App\Http\Requests;

use App\Models\OrderTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
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
        ];
    }

    private function allowedOrderTimes(): array
    {
        return array_merge(OrderTime::pluck('name')->all(), ['All']);
    }
}
