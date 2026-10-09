<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by the 3 roles' "add single item to the pre-order cart"
 * actions — see UpdateOrderItemQuantityRequest for why this is called
 * manually (rules()) instead of auto-injected.
 */
class AddToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'qty' => 'required',
            'branch_code' => 'required',
        ];
    }
}
