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
                    ->where(fn ($query) => $query
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
}

