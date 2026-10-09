<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by the two 'date' checks inside Rep\CompleteOrders::index() —
 * already identical.
 */
class CompleteOrdersDateFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'date' => 'required',
        ];
    }
}
