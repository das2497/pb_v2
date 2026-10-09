<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rep's one-time form-token guard on addAllToCart().
 */
class VerifyFormTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'form_token' => 'required',
        ];
    }
}
