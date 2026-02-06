<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
                'sometimes',
                'nullable',
                'string',
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:191',
                Rule::unique('users')
                    ->where(
                        fn($query) => $query
                            ->where('email', $this->email)
                            ->where('phoneNumber', $this->phoneNumber)
                    )
                    ->ignore($this->route('id')),
            ],
            'phoneNumber' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'role' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::in([1, 2, 3]),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.string' => 'A név szöveg kell legyen!',
            'email.email' => 'Az email formátuma nem megfelelő!',
            'email.max' => 'Az email hossza max: 191!',
            'email.unique' => 'Ez az email már foglalt!',
            'phoneNumber.string' => 'A telefonszám szöveg kell legyen!',
            'phoneNumber.max' => 'A telefonszám hossza max: 20!',
            'password.string' => 'A jelszó szöveg kell legyen!',
            'role.integer' => 'A szerepkör egész szám kell legyen!',
            'role.in' => 'A szerepkör értéke nem megengedett!',
        ];
    }
}
