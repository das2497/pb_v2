<?php

namespace App\Http\Requests;

use App\Models\OrderTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'item_number' => 'required',
            'item_name_e' => 'required',
            'item_name_s' => 'required',
            'category' => 'required',
            'visibility' => 'required',
            'order_time' => ['required', 'array', 'min:1'],
            'order_time.*' => ['string', Rule::in($this->allowedOrderTimes())],
            'pb_unit_price' => 'required',
            'pb_mrp' => 'required',
            'pb_direct_sale_price' => 'required',
        ];
    }

    private function allowedOrderTimes(): array
    {
        return array_merge(OrderTime::pluck('name')->all(), ['All']);
    }
}
