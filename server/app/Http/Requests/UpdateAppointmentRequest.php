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
                    ->where(
                        fn($query) => $query
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
    public function messages(): array
    {
        return [
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'barberId.unique' => 'Erre az időpontra már van foglalás ennél a borbélynál!',
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'appointmentDate.date' => 'Az időpont dátuma érvényes dátum kell legyen!',
            'appointmentTime.date_format' => 'Az időpont ideje formátuma H:i kell legyen!',
            'status.in' => 'A státusz értéke nem megengedett!',
            'cancelledBy.in' => 'A lemondó értéke nem megengedett!',
        ];
    }
}
