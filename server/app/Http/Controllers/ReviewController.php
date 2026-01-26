<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::orderBy('barberId')->orderBy('id')->get();

        return response()->json($reviews);
    }
    public function store(Request $request, int $appointmentId)
    {

        $user = auth()->user();

        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->userId !== $user->id) {
            return response()->json([
                'error' => 'You are not authorized to review this appointment'
            ], 403);
        }

        if (Review::where('appointmentId', $appointmentId)->exists()) {
            return response()->json([
                'error' => 'Review for this appointment already exists'
            ], 409);
        }
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $newReview = Review::create([
            'appointmentId' => $appointmentId,
            'barberId' => $appointment->barberId,
            'userId' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);
        return response()->json($newReview, 201);
    }

    public function destroy(int $id)
    {
        $user = auth()->user();

        $row = Review::find($id);

        if (!$row) {
            return response()->json([
                'error' => 'Review not found'
            ], 404);
        }

        // Ellenőrzés role szerint
        if ($user->isAdmin()) {
            // Admin mindent törölhet
        } elseif ($user->isBarber() && $row->barberId !== $user->id) {
            return response()->json([
                'error' => 'Forbidden: You cannot delete this review'
            ], 403);
        } elseif ($user->isCustomer() && $row->userId !== $user->id) {
            return response()->json([
                'error' => 'Forbidden: You cannot delete this review'
            ], 403);
        }

        // Törlés
        $row->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
            'id' => $id
        ], 200);
    }
}
