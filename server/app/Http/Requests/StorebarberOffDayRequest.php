<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorebarberOffDayRequest extends FormRequest
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
            'offDay' => [
                'required',
                'date',
                Rule::unique('barber_off_days', 'offDay')
                    ->where(fn($query) => $query->where('barberId', $this->barberId)),
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'barberId.required' => 'A borbély azonosító megadása kötelező!',
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'offDay.required' => 'A szabadnap megadása kötelező!',
            'offDay.date' => 'A szabadnap érvényes dátum kell legyen!',
            'offDay.unique' => 'Erre a napra már van rögzített szabadnap!',
        ];
    }
}
