<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class AppointmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user?->isBarber()) {
            return Appointment::query()
                ->where('barberId', $user->barber->id)
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'user'])
                ->get();
        }

        if ($user?->isAdmin()) {
            return Appointment::query()
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'user', 'barber'])
                ->get();
        }

        return Appointment::query()
            ->where('userId', $user->id)
            ->orderByDesc('appointmentDate')
            ->orderByDesc('appointmentTime')
            ->with(['services', 'barber'])
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barberId' => ['required', 'integer', 'exists:barbers,id'],
            'appointmentDate' => ['required', 'date'],
            'appointmentTime' => ['required', 'date_format:H:i'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        $isOffDay = DB::table('barber_off_days')
            ->where('barberId', $data['barberId'])
            ->where('offDay', $data['appointmentDate'])
            ->exists();

        if ($isOffDay) {
            return response()->json([
                'message' => 'A barber ezen a napon szabadságon van.',
                'data' => null
            ], 422, options: JSON_UNESCAPED_UNICODE);
        }

        try {
            $appointment = DB::transaction(function () use ($data) {
                $appointment = Appointment::create([
                    'barberId' => $data['barberId'],
                    'userId' => auth()->id(),
                    'appointmentDate' => $data['appointmentDate'],
                    'appointmentTime' => $data['appointmentTime'],
                    'status' => 'booked',
                    'cancelledBy' => 'none',
                ]);

                $appointment->services()->sync($data['services']);

                return $appointment->load(['services', 'barber']);
            });

            return response()->json([
                'message' => 'Created',
                'data' => $appointment
            ], 201, options: JSON_UNESCAPED_UNICODE);

        } catch (QueryException $e) {
            $mysqlCode = $e->errorInfo[1] ?? null;

            if ($mysqlCode === 1062) {
                return response()->json([
                    'message' => 'Ez az időpont már foglalt ennél a borbélynál.',
                    'data' => null
                ], 409, options: JSON_UNESCAPED_UNICODE);
            }

            throw $e;
        }
    }

    public function show(int $id)
    {
        $appointment = Appointment::query()
            ->with(['services', 'barber', 'user'])
            ->findOrFail($id);

        $user = auth()->user();

        abort_unless(
            $user?->isAdmin() ||
            $appointment->userId === $user->id ||
            ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
            403
        );

        return $appointment;
    }

    public function destroy(int $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = auth()->user();

        abort_unless(
            $user?->isAdmin() ||
            $appointment->userId === $user->id ||
            ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
            403
        );

        $appointment->delete();

        return response()->json([
            'message' => 'Cancelled',
            'data' => null
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }
}
