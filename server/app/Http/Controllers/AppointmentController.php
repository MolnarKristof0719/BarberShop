<?php

namespace App\Http\Controllers;

use App\Mail\BookingCreatedMail;
use App\Models\Appointment as CurrentModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function index()
    {
        return $this->apiResponse(function () {
            $user = auth()->user();

            if ($user?->isBarber()) {
                $barberId = $user->barber?->id;
                abort_unless((bool) $barberId, 403);

                return CurrentModel::query()
                    ->where('barberId', $barberId)
                    ->orderByDesc('appointmentDate')
                    ->orderByDesc('appointmentTime')
                    ->with(['services', 'user'])
                    ->get();
            }

            if ($user?->isAdmin()) {
                return CurrentModel::query()
                    ->orderByDesc('appointmentDate')
                    ->orderByDesc('appointmentTime')
                    ->with(['services', 'user', 'barber'])
                    ->get();
            }

            abort_unless((bool) $user, 403);

            return CurrentModel::query()
                ->where('userId', $user->id)
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'barber'])
                ->get();
        });
    }

    public function store(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $data = $request->validate([
                'barberId' => ['required', 'integer', 'exists:barbers,id'],
                'appointmentDate' => ['required', 'date'],
                'appointmentTime' => ['required', 'date_format:H:i'],
                'services' => ['required', 'array', 'min:1'],
                'services.*' => ['integer', 'exists:services,id'],
            ]);

            $isBarberActive = DB::table('barbers')
                ->where('id', $data['barberId'])
                ->where('isActive', true)
                ->exists();

            if (!$isBarberActive) {
                abort(422, 'A barber jelenleg nem aktiv.');
            }

            $isOffDay = DB::table('barber_off_days')
                ->where('barberId', $data['barberId'])
                ->where('offDay', $data['appointmentDate'])
                ->exists();

            if ($isOffDay) {
                abort(422, 'A barber ezen a napon szabadsagon van.');
            }

            try {
                return DB::transaction(function () use ($data) {
                    $appointment = CurrentModel::create([
                        'barberId' => $data['barberId'],
                        'userId' => auth()->id(),
                        'appointmentDate' => $data['appointmentDate'],
                        'appointmentTime' => $data['appointmentTime'],
                        'status' => 'booked',
                        'cancelledBy' => 'none',
                    ]);

                    $appointment->services()->sync($data['services']);
                    $appointment->load(['services', 'barber.user', 'user']);

                    Mail::to($appointment->user->email)->send(new BookingCreatedMail($appointment));

                    return $appointment;
                });
            } catch (QueryException $e) {
                $mysqlCode = $e->errorInfo[1] ?? null;

                if ($mysqlCode === 1062) {
                    abort(409, 'Ez az idopont mar foglalt ennel a barbernel.');
                }

                throw $e;
            }
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $appointment = CurrentModel::query()
                ->with(['services', 'barber', 'user'])
                ->find($id);

            abort_if(!$appointment, 404, 'Az idopont nem talalhato.');

            return $appointment;
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $appointment = CurrentModel::query()->with(['services', 'barber', 'user'])->findOrFail($id);
            $user = auth()->user();

            abort_unless(
                $user?->isAdmin() ||
                $appointment->userId === $user?->id ||
                ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
                403
            );

            if ($appointment->status === 'completed') {
                abort(422, 'Completed idopont nem mondhato le.');
            }

            if ($appointment->status !== 'cancelled') {
                $cancelledBy = $appointment->userId === $user?->id ? 'customer' : 'barber';

                $appointment->update([
                    'status' => 'cancelled',
                    'cancelledBy' => $cancelledBy,
                ]);

                $appointment->refresh()->load(['services', 'barber', 'user']);
            }

            return $appointment;
        });
    }
}
