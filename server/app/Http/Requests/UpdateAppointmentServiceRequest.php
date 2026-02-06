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
                    ->where(fn($query) => $query->where('serviceId', $this->serviceId))
                    ->ignore($this->route('id')),
            ],
            'serviceId' => [
                'sometimes',
                'integer',
                'exists:services,id',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'appointmentId.integer' => 'A foglalás azonosító egész szám kell legyen!',
            'appointmentId.exists' => 'A megadott foglalás nem létezik!',
            'appointmentId.unique' => 'Ehhez a foglaláshoz ez a szolgáltatás már hozzá van adva!',
            'serviceId.integer' => 'A szolgáltatás azonosító egész szám kell legyen!',
            'serviceId.exists' => 'A megadott szolgáltatás nem létezik!',
        ];
    }
}
