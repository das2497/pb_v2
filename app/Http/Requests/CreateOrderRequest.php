<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by OrderAdmin's and Rep's "create order from cart" actions.
 * Shop's equivalent has its own rule (see CreateShopOrderRequest) since
 * Shop alone supports the Default/standing-order path. See
 * UpdateOrderItemQuantityRequest for why this is called manually
 * (rules()) instead of auto-injected.
 */
class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_time' => 'required',
        ];
    }
}
