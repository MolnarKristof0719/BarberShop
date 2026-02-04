<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReferencePictureRequest extends FormRequest
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
                'required',
                'integer',
                'exists:barbers,id',
            ],
            'picture' => [
                'required',
                'string',
                'max:125',
                Rule::unique('reference_pictures', 'picture')
                    ->where(fn ($query) => $query->where('barberId', $this->barberId)),
            ],
        ];
    }
}
