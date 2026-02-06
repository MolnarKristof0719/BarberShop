<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBarberRequest extends FormRequest
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
                'sometimes',
                'integer',
                'exists:users,id',
            ],
            'profilePicture' => [
                'sometimes',
                'string',
                'max:125',
                Rule::unique('barbers', 'profilePicture')
                    ->where(fn($query) => $query->where('userId', $this->userId))
                    ->ignore($this->route('id')),
            ],
            'introduction' => [
                'sometimes',
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
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'profilePicture.string' => 'A profilkép szöveg kell legyen!',
            'profilePicture.max' => 'A profilkép hossza max: 125!',
            'profilePicture.unique' => 'A profilkép már létezik!',
            'introduction.string' => 'A bemutatkozás szöveg kell legyen!',
            'isActive.boolean' => 'Az aktív státusz logikai érték kell legyen!',
        ];
    }
}
