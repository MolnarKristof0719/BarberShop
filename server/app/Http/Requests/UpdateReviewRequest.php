<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReviewRequest extends FormRequest
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
                Rule::unique('reviews')
                    ->where(
                        fn($query) => $query
                            ->where('appointmentId', $this->appointmentId)
                            ->where('barberId', $this->barberId)
                            ->where('userId', $this->userId)
                    )
                    ->ignore($this->route('id')),
            ],
            'barberId' => [
                'sometimes',
                'integer',
                'exists:barbers,id',
            ],
            'userId' => [
                'sometimes',
                'integer',
                'exists:users,id',
            ],
            'rating' => [
                'sometimes',
                'integer',
            ],
            'comment' => [
                'sometimes',
                'string',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'appointmentId.integer' => 'A foglalás azonosító egész szám kell legyen!',
            'appointmentId.exists' => 'A megadott foglalás nem létezik!',
            'appointmentId.unique' => 'Ehhez a foglaláshoz már van értékelés!',
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'rating.integer' => 'Az értékelés egész szám kell legyen!',
            'comment.string' => 'A komment szöveg kell legyen!',
        ];
    }
}
