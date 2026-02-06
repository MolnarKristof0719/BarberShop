<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserSelfRequest extends FormRequest
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
                'required',
                'string',
                'required_without_all:email,phoneNumber'
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:191',
                'required_without_all:name,phoneNumber',
                Rule::unique('users')
                    ->where(
                        fn($query) => $query
                            ->where('email', $this->email)
                            ->where('phoneNumber', $this->phoneNumber)
                    )
                    ->ignore($this->user()->id),
            ],
            'phoneNumber' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'required_without_all:name,email',
            ],
            'role' => 'prohibited',
            'password' => 'prohibited',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'A név megadása kötelező!',
            'name.string' => 'A név szöveg kell legyen!',
            'name.required_without_all' => 'A név megadása kötelező, ha nincs email és telefonszám!',
            'email.required' => 'Az email megadása kötelező!',
            'email.email' => 'Az email formátuma nem megfelelő!',
            'email.max' => 'Az email hossza max: 191!',
            'email.unique' => 'Ez az email már foglalt!',
            'email.required_without_all' => 'Az email megadása kötelező, ha nincs név és telefonszám!',
            'phoneNumber.required' => 'A telefonszám megadása kötelező!',
            'phoneNumber.string' => 'A telefonszám szöveg kell legyen!',
            'phoneNumber.max' => 'A telefonszám hossza max: 20!',
            'phoneNumber.required_without_all' => 'A telefonszám megadása kötelező, ha nincs név és email!',
            'role.prohibited' => 'A szerepkör nem küldhető!',
            'password.prohibited' => 'A jelszó nem küldhető!',
        ];
    }
}
