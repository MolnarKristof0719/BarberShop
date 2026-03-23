<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
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
            'service' => [
                'required',
                'string',
                'max:50',
                Rule::unique('services', 'service'),
            ],
            'price' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'service.required' => 'A szolgáltatás megadása kötelező!',
            'service.string' => 'A szolgáltatás szöveg kell legyen!',
            'service.max' => 'A szolgáltatás hossza max: 50!',
            'service.unique' => 'A szolgáltatás már létezik!',
            'price.required' => 'Az ár megadása kötelező!',
            'price.integer' => 'Az ár csak egész szám lehet!',
            'price.min' => 'Az ár nem lehet negatív!',
        ];
    }
}
