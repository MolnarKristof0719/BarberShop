<?php

namespace App\Http\Controllers;

use App\Models\Appointment as CurrentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

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

            $isOffDay = DB::table('barber_off_days')
                ->where('barberId', $data['barberId'])
                ->where('offDay', $data['appointmentDate'])
                ->exists();

            if ($isOffDay) {
                // 422, és a base Controllered HttpException-ként kezeli
                abort(422, 'A barber ezen a napon szabadságon van.');
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

                    return $appointment->load(['services', 'barber']);
                });
            } catch (QueryException $e) {
                $mysqlCode = $e->errorInfo[1] ?? null;

                if ($mysqlCode === 1062) {
                    abort(409, 'Ez az időpont már foglalt ennél a borbélynál.');
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

            abort_if(!$appointment, 404, 'Az időpont nem található.');

            $user = auth()->user();

            // abort_unless(
            //     $user?->isAdmin() ||
            //     $appointment->userId === $user?->id ||
            //     ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
            //     403,
            //     'Nincs jogosultságod ehhez az időponthoz.'
            // );

            return $appointment;
        });
    }


    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $appointment = CurrentModel::findOrFail($id);
            $user = auth()->user();

            abort_unless(
                $user?->isAdmin() ||
                $appointment->userId === $user?->id ||
                ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
                403
            );

            $appointment->delete();

            // Ha ragaszkodsz a régihez: return null; és akkor data=null lesz
            return null;
        });
    }
}
