<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppointmentServiceRequest extends FormRequest
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
            'appointmentId' => [
                'sometimes',
                'integer',
                'exists:appointments,id',
                Rule::unique('appointment_services')
                    ->where(fn ($query) => $query->where('serviceId', $this->serviceId))
                    ->ignore($this->route('id')),
            ],
            'serviceId' => [
                'sometimes',
                'integer',
                'exists:services,id',
            ],
        ];
    }
}
