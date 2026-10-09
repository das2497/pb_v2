<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderTimeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|not_in:All,all,Both,both|unique:order_times,name',
        ];
    }
}
