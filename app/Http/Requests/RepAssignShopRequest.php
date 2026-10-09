<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RepAssignShopRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rep_id' => 'required',
            'shop_id' => 'required',
        ];
    }
}
