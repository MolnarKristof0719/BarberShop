<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
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
                'required',
                'integer',
                'exists:appointments,id',
                Rule::unique('reviews')
                    ->where(
                        fn($query) => $query
                            ->where('appointmentId', $this->appointmentId)
                            ->where('barberId', $this->barberId)
                            ->where('userId', $this->userId)
                    ),
            ],
            'barberId' => [
                'required',
                'integer',
                'exists:barbers,id',
            ],
            'userId' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'rating' => [
                'required',
                'integer',
            ],
            'comment' => [
                'required',
                'string',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'appointmentId.required' => 'A foglalás azonosító megadása kötelező!',
            'appointmentId.integer' => 'A foglalás azonosító egész szám kell legyen!',
            'appointmentId.exists' => 'A megadott foglalás nem létezik!',
            'appointmentId.unique' => 'Ehhez a foglaláshoz már van értékelés!',
            'barberId.required' => 'A borbély azonosító megadása kötelező!',
            'barberId.integer' => 'A borbély azonosító egész szám kell legyen!',
            'barberId.exists' => 'A megadott borbély nem létezik!',
            'userId.required' => 'A felhasználó azonosító megadása kötelező!',
            'userId.integer' => 'A felhasználó azonosító egész szám kell legyen!',
            'userId.exists' => 'A megadott felhasználó nem létezik!',
            'rating.required' => 'Az értékelés megadása kötelező!',
            'rating.integer' => 'Az értékelés egész szám kell legyen!',
            'comment.required' => 'A komment megadása kötelező!',
            'comment.string' => 'A komment szöveg kell legyen!',
        ];
    }
}
