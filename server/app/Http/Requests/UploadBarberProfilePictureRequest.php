<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadBarberProfilePictureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profilePicture' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profilePicture.required' => 'A profilkep file megadasa kotelezo.',
            'profilePicture.file' => 'A profilePicture mezo csak file lehet.',
            'profilePicture.mimes' => 'A profilkep csak jpg, jpeg, png vagy webp lehet.',
            'profilePicture.max' => 'A profilkep maximum 5 MB lehet.',
        ];
    }
}

