<?php

namespace App\Http\Controllers;

use App\Mail\BookingCancelledByOffDayMail;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberOffDay as CurrentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BarberOffDayController extends Controller
{
    public function index()
    {
        return $this->apiResponse(function () {
            $user = auth()->user();

            if ($user?->isAdmin()) {
                return CurrentModel::query()
                    ->orderBy('barberId')
                    ->orderBy('offDay')
                    ->get();
            }

            if ($user?->isBarber()) {
                $barberId = $user->barber?->id;
                abort_unless($barberId, 403);

                return CurrentModel::query()
                    ->where('barberId', $barberId)
                    ->orderBy('offDay')
                    ->get();
            }

            abort(403);
        });
    }

    public function store(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $user = auth()->user();

            if ($user?->isAdmin()) {
                throw new HttpException(403, 'Adminkent nem adhatsz hozza szabadnapot.');
            }

            if (!$user?->isBarber()) {
                throw new HttpException(403, 'Csak barber adhat hozza szabadnapot.');
            }

            $barberId = $user->barber?->id;
            abort_unless($barberId, 403);

            $data = $request->validate([
                'offDay' => ['required', 'date'],
            ]);

            $exists = CurrentModel::query()
                ->where('barberId', $barberId)
                ->where('offDay', $data['offDay'])
                ->exists();

            if ($exists) {
                throw new HttpException(422, 'Ez a nap mar szabadnapkent szerepel.');
            }

            $result = DB::transaction(function () use ($barberId, $data) {
                $offDay = CurrentModel::create([
                    'barberId' => $barberId,
                    'offDay' => $data['offDay'],
                ]);

                $affectedAppointments = Appointment::query()
                    ->where('barberId', $barberId)
                    ->whereDate('appointmentDate', $data['offDay'])
                    ->where('status', 'booked')
                    ->with(['user', 'barber.user', 'services'])
                    ->lockForUpdate()
                    ->get();

                foreach ($affectedAppointments as $appointment) {
                    $appointment->update([
                        'status' => 'cancelled',
                        'cancelledBy' => 'barber',
                    ]);
                }

                if ($data['offDay'] === now()->toDateString()) {
                    Barber::query()->where('id', $barberId)->update(['isActive' => false]);
                }

                return [
                    'offDay' => $offDay,
                    'affectedAppointments' => $affectedAppointments,
                ];
            });

            foreach ($result['affectedAppointments'] as $appointment) {
                if (!empty($appointment->user?->email)) {
                    Mail::to($appointment->user->email)->send(new BookingCancelledByOffDayMail($appointment));
                }
            }

            return [
                'offDay' => $result['offDay'],
                'cancelledAppointmentsCount' => $result['affectedAppointments']->count(),
            ];
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $user = auth()->user();
            $row = CurrentModel::findOrFail($id);

            if ($user?->isAdmin()) {
                $row->delete();
                return ['id' => $id];
            }

            abort_unless($user?->isBarber(), 403);

            $barberId = $user->barber?->id;
            abort_unless($barberId, 403);
            abort_unless($row->barberId === $barberId, 403);

            $isToday = $row->offDay?->format('Y-m-d') === now()->toDateString();
            $row->delete();

            if ($isToday) {
                $hasTodayOffDay = CurrentModel::query()
                    ->where('barberId', $barberId)
                    ->whereDate('offDay', now()->toDateString())
                    ->exists();

                if (!$hasTodayOffDay) {
                    Barber::query()->where('id', $barberId)->update(['isActive' => true]);
                }
            }

            return ['id' => $id];
        });
    }
}
