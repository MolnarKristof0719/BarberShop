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
                    ->where(fn ($query) => $query
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
}
