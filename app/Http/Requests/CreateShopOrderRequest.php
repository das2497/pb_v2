<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shop's "create order from cart" action, distinct from
 * CreateOrderRequest because Shop alone supports the Default/standing-
 * order path, where order_time is optional (the "default" checkbox is
 * sent instead).
 */
class CreateShopOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_time' => 'required_without:default',
        ];
    }
}
