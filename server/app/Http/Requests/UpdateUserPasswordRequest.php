<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Mivel a bejelentkezett user a saját jelszavát módosítja, ez true
        return true;
    }

    public function rules(): array
    {
        return [
            'newpassword' => ['required', 'string', Password::min(3), 'confirmed'],
        ];
    }
    public function messages(): array
    {
        return [
            'newpassword.required' => 'Az új jelszó megadása kötelező!',
            'newpassword.string' => 'Az új jelszó szöveg kell legyen!',
            'newpassword.min' => 'Az új jelszó hossza min: 3!',
            'newpassword.confirmed' => 'Az új jelszó megerősítése nem egyezik!',
        ];
    }
}
