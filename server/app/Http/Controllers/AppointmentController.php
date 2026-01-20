<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAppointmentRequest $request)
    {
        $data = $request->validate([
            'barberId' => ['required', 'exists:barbers,id'],
            'userId' => ['required', 'exists:users,id'],
            'appointmentDate' => ['required', 'date'],
            // ha nálad "13:30" jön, akkor date_format:H:i; ha "13:30:00", akkor H:i:s
            'appointmentTime' => ['required', 'date_format:H:i:s'],
        ]);

        // 1) OFF DAY tiltás
        $isOffDay = DB::table('barber_off_days')
            ->where('barberId', $data['barberId'])
            ->where('offDay', $data['appointmentDate'])
            ->exists();

        if ($isOffDay) {
            return response()->json([
                'message' => 'A barber ezen a napon szabadságon van, nem foglalható időpont.'
            ], 422);
        }

        // 2) Foglalás mentése (unique ütközést kezeljük)
        try {
            $appointment = Appointment::create([
                'barberId' => $data['barberId'],
                'userId' => $data['userId'],
                'appointmentDate' => $data['appointmentDate'],
                'appointmentTime' => $data['appointmentTime'],
                'status' => 'booked',
                'cancelledBy' => 'none',
            ]);
        } catch (QueryException $e) {
            // MySQL unique violation tipikusan 23000
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ez az időpont már foglalt ennél a borbélynál.'
                ], 409);
            }

            throw $e;
        }

        return response()->json($appointment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }
}
