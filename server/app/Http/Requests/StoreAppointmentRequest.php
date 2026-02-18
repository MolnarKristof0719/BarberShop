<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
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
                Rule::unique('appointments')
                    ->where(
                        fn($query) => $query
                            ->where('barberId', $this->barberId)
                            ->where('appointmentDate', $this->appointmentDate)
                            ->where('appointmentTime', $this->appointmentTime)
                    ),
            ],
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'appointmentDate' => [
                'required',
                'date',
            ],
            'appointmentTime' => [
                'required',
                'date_format:H:i',
            ],
            'status' => [
                'sometimes',
                Rule::in(['booked', 'cancelled', 'completed', 'done']),
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
            'barberId.required' => 'A borbély azonosító megadása kötelező!',
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'barberId.unique' => 'Erre az időpontra már van foglalás ennél a borbélynál!',
            'userId.required' => 'A felhasználó azonosító megadása kötelező!',
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'appointmentDate.required' => 'Az időpont dátumának megadása kötelező!',
            'appointmentDate.date' => 'Az időpont dátuma érvényes dátum kell legyen!',
            'appointmentTime.required' => 'Az időpont idejének megadása kötelező!',
            'appointmentTime.date_format' => 'Az időpont ideje formátuma H:i kell legyen!',
            'status.in' => 'A státusz értéke nem megengedett!',
            'cancelledBy.in' => 'A lemondó értéke nem megengedett!',
        ];
    }
}

