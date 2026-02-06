<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('users')
                    ->where(
                        fn($query) => $query
                            ->where('email', $this->email)
                            ->where('phoneNumber', $this->phoneNumber)
                    ),
            ],
            'phoneNumber' => [
                'nullable',
                'string',
                'max:20',
            ],
            'password' => [
                'required',
                'string',
            ],
            'role' => [
                'sometimes',
                'integer',
                Rule::in([1, 2, 3]),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'A név megadása kötelező!',
            'name.string' => 'A név szöveg kell legyen!',
            'email.required' => 'Az email megadása kötelező!',
            'email.email' => 'Az email formátuma nem megfelelő!',
            'email.max' => 'Az email hossza max: 191!',
            'email.unique' => 'Ez az email már foglalt!',
            'phoneNumber.string' => 'A telefonszám szöveg kell legyen!',
            'phoneNumber.max' => 'A telefonszám hossza max: 20!',
            'password.required' => 'A jelszó megadása kötelező!',
            'password.string' => 'A jelszó szöveg kell legyen!',
            'role.integer' => 'A szerepkör egész szám kell legyen!',
            'role.in' => 'A szerepkör értéke nem megengedett!',
        ];
    }
}
