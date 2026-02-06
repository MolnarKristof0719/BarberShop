<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'email' => 'required|email|max:191',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Az email megadása kötelező!',
            'email.email' => 'Az email formátuma nem megfelelő!',
            'email.max' => 'Az email hossza max: 191!',
            'password.required' => 'A jelszó megadása kötelező!',
        ];
    }
}
