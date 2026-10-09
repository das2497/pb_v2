<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AIChatMessageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:500',
            'model' => 'required|string',
        ];
    }
}
