<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBarberRequest extends FormRequest
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
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'profilePicture' => [
                'required',
                'string',
                'max:125',
                Rule::unique('barbers', 'profilePicture')
                    ->where(fn ($query) => $query->where('userId', $this->userId)),
            ],
            'introduction' => [
                'required',
                'string',
            ],
            'isActive' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
