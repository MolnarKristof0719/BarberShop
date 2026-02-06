<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReferencePictureRequest extends FormRequest
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
            'barberId' => [
                'sometimes',
                'integer',
                'exists:barbers,id',
            ],
            'picture' => [
                'sometimes',
                'string',
                'max:125',
                Rule::unique('reference_pictures', 'picture')
                    ->where(fn($query) => $query->where('barberId', $this->barberId))
                    ->ignore($this->route('id')),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'picture.string' => 'A kép szöveg kell legyen!',
            'picture.max' => 'A kép hossza max: 125!',
            'picture.unique' => 'A kép már létezik!',
        ];
    }
}
