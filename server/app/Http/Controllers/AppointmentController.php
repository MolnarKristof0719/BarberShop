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

        // Barber: a saját foglalásai (neki hasznos látni a usert is)
        if ($user?->isBarber()) {
            $rows = Appointment::query()
                ->where('barberId', $user->barber->id)
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'user'])
                ->get();

            return response()->json([
                'message' => 'OK',
                'data' => $rows
            ], 200, options: JSON_UNESCAPED_UNICODE);
        }

        // Admin: mindent láthat (ha ezt akarjátok)
        if ($user?->isAdmin()) {
            $rows = Appointment::query()
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'user', 'barber'])
                ->get();

            return response()->json([
                'message' => 'OK',
                'data' => $rows
            ], 200, options: JSON_UNESCAPED_UNICODE);
        }

        // User: saját foglalásai
        $rows = Appointment::query()
            ->where('userId', $user->id)
            ->orderByDesc('appointmentDate')
            ->orderByDesc('appointmentTime')
            ->with(['services', 'barber'])
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $rows
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barberId' => ['required', 'integer', 'exists:barbers,id'],
            'appointmentDate' => ['required', 'date'],
            'appointmentTime' => ['required', 'date_format:H:i'], // ✅ H:i
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer', 'exists:services,id'],
        ]);

        // 1) OFF DAY tiltás
        $isOffDay = DB::table('barber_off_days')
            ->where('barberId', $data['barberId'])
            ->where('offDay', $data['appointmentDate'])
            ->exists();

        if ($isOffDay) {
            return response()->json([
                'message' => 'A barber ezen a napon szabadságon van, nem foglalható időpont.',
                'data' => null
            ], 422, options: JSON_UNESCAPED_UNICODE);
        }

        // 2) Mentés + pivot egy tranzakcióban
        try {
            $appointment = DB::transaction(function () use ($data) {
                $appointment = Appointment::create([
                    'barberId' => $data['barberId'],
                    'userId' => auth()->id(),          // ✅ userId nem jön requestből
                    'appointmentDate' => $data['appointmentDate'],
                    'appointmentTime' => $data['appointmentTime'],
                    'status' => 'booked',
                    'cancelledBy' => 'none',           // ✅ DB miatt kell
                ]);

                $appointment->services()->sync($data['services']);

                return $appointment->load(['services', 'barber']);
            });

            return response()->json([
                'message' => 'Created',
                'data' => $appointment
            ], 201, options: JSON_UNESCAPED_UNICODE);

        } catch (QueryException $e) {
            // ✅ csak a 1062 = Duplicate entry (unique) legyen "foglalt"
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

        return response()->json([
            'message' => 'OK',
            'data' => $appointment
        ], 200, options: JSON_UNESCAPED_UNICODE);
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

        // Egyszerű: törlés. (Ha inkább "status=cancelled" kell, szólj és átírjuk.)
        $appointment->delete();

        return response()->json([
            'message' => 'Cancelled',
            'data' => null
        ], 200, options: JSON_UNESCAPED_UNICODE);
    }
}
