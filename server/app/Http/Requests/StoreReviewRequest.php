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
                    ->where(fn ($query) => $query
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
}
