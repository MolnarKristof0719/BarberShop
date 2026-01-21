<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, int $appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        abort_unless($appointment->userId === auth()->id(), 403);

        abort_unless(!Review::where('appointmentId', $appointmentId)->exists(), 409);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        return Review::create([
            'appointmentId' => $appointmentId,
            'barberId' => $appointment->barberId,
            'userId' => auth()->id(),
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);
    }
}
