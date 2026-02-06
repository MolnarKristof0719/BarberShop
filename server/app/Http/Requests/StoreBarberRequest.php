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
                    ->where(fn($query) => $query->where('userId', $this->userId)),
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
    public function messages(): array
    {
        return [
            'userId.required' => 'A felhasználó azonosító megadása kötelező!',
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'profilePicture.required' => 'A profilkép megadása kötelező!',
            'profilePicture.string' => 'A profilkép szöveg kell legyen!',
            'profilePicture.max' => 'A profilkép hossza max: 125!',
            'profilePicture.unique' => 'A profilkép már létezik!',
            'introduction.required' => 'A bemutatkozás megadása kötelező!',
            'introduction.string' => 'A bemutatkozás szöveg kell legyen!',
            'isActive.boolean' => 'Az aktív státusz logikai érték kell legyen!',
        ];
    }
}
