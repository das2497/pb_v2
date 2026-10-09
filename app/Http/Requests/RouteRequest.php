<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by RouteProcess::store() and ::update() — already identical.
 */
class RouteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'index' => 'required',
            'name' => 'required',
            'type' => 'required',
            'time' => 'required',
        ];
    }
}
