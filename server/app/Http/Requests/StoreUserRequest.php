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
                    ->where(fn ($query) => $query
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
}
