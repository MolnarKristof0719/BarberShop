<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Review as CurrentModel;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return $this->apiResponse(function () {
            return CurrentModel::query()
                ->orderBy('barberId')
                ->orderBy('id')
                ->get();
        });
    }

    public function store(Request $request, int $appointmentId)
    {
        return $this->apiResponse(function () use ($request, $appointmentId) {
            $user = auth()->user();
            abort_unless((bool) $user, 403, 'Unauthorized');

            // Ne findOrFail: stabil 404 üzenet
            $appointment = Appointment::query()->find($appointmentId);
            abort_if(!$appointment, 404, 'Appointment not found');

            abort_unless(
                $appointment->userId === $user->id,
                403,
                'You are not authorized to review this appointment'
            );

            $exists = CurrentModel::query()
                ->where('appointmentId', $appointmentId)
                ->exists();

            abort_unless(
                !$exists,
                409,
                'Review for this appointment already exists'
            );

            $data = $request->validate([
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'comment' => ['nullable', 'string'],
            ]);

            return CurrentModel::create([
                'appointmentId' => $appointmentId,
                'barberId' => $appointment->barberId,
                'userId' => $user->id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $user = auth()->user();
            abort_unless((bool) $user, 403, 'Unauthorized');

            // Ne findOrFail: stabil 404 üzenet
            $row = CurrentModel::query()->find($id);
            abort_if(!$row, 404, 'Review not found');

            if ($user->isAdmin()) {
                // admin mindent törölhet
            } elseif ($user->isBarber()) {
                // FIGYELEM: a reviews.barberId a barbers.id (nem users.id)
                $barberId = $user->barber?->id;
                abort_unless((bool) $barberId, 403, 'Barber profile not found');

                abort_unless(
                    $row->barberId === $barberId,
                    403,
                    'Forbidden: You cannot delete this review'
                );
            } elseif ($user->isCustomer()) {
                abort_unless(
                    $row->userId === $user->id,
                    403,
                    'Forbidden: You cannot delete this review'
                );
            } else {
                abort(403, 'Forbidden');
            }

            $row->delete();

            return ['id' => $id];
        });
    }
}
