<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentRequest extends FormRequest
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
                Rule::unique('appointments')
                    ->where(fn ($query) => $query
                        ->where('barberId', $this->barberId)
                        ->where('appointmentDate', $this->appointmentDate)
                        ->where('appointmentTime', $this->appointmentTime)
                    )
                    ->ignore($this->route('id')),
            ],
            'userId' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],
            'appointmentDate' => [
                'sometimes',
                'date',
            ],
            'appointmentTime' => [
                'sometimes',
                'date_format:H:i',
            ],
            'status' => [
                'sometimes',
                Rule::in(['booked', 'cancelled', 'completed']),
            ],
            'cancelledBy' => [
                'sometimes',
                Rule::in(['none', 'barber', 'customer']),
            ],
        ];
    }
}
