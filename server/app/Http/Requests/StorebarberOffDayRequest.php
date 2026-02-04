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
                    ->where(fn ($query) => $query->where('barberId', $this->barberId)),
            ],
        ];
    }
}
