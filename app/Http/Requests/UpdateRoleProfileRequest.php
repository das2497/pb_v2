<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared by Shop\Profile::update() and Rep\Profile::update() — the two
 * were already byte-identical.
 */
class UpdateRoleProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:8',
            'confirm' => 'required',
        ];
    }
}
