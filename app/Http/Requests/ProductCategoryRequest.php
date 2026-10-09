<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by ProductCategory::store() and ::update() — already identical.
 */
class ProductCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'main_category' => 'required',
            'category' => 'required',
        ];
    }
}
