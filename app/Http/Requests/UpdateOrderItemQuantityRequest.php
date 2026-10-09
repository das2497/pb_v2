<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The exact validation every "update cart/order item quantity" and "add
 * item to cart/order" action across OrderAdmin/Rep/Shop already ran
 * inline before it was consolidated here — genuinely identical in all
 * 18 places, not merged for convenience.
 *
 * Used via rules() called from inside each controller action (not as an
 * auto-injected method parameter): several of those actions run other
 * checks (an access gate, a cart-empty check, ...) before validating
 * today, and auto-injecting a FormRequest would run validation before
 * the controller method body even starts, silently reordering which
 * error a bad request sees first. Calling $request->validate($this->rules())
 * at the original call site preserves that order exactly.
 */
class UpdateOrderItemQuantityRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'qty' => 'required',
        ];
    }
}
