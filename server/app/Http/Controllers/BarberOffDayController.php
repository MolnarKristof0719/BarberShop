<?php

namespace App\Http\Controllers;

use App\Models\barberOffDay;
use App\Http\Requests\StorebarberOffDayRequest;
use App\Http\Requests\UpdatebarberOffDayRequest;

use Illuminate\Support\Facades\DB;

class BarberOffDayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $rows = barberOffDay::all();
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $rows
            ];
        } catch (\Exception $e) {
            $status = 500;
            $data = [
                'message' => "Server error: {$e->getCode()}",
                'data' => $rows
            ];
        }
        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorebarberOffDayRequest $request)
    {
        $data = $request->validate([
            'barberId' => ['required', 'exists:barbers,id'],
            'offDay' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($data) {

            // 1) Off day beszúrás (unique miatt insertOrIgnore)
            DB::table('barber_off_days')->insertOrIgnore([
                'barberId' => $data['barberId'],
                'offDay'   => $data['offDay'],
            ]);

            // 2) Minden azon a napon lévő foglalás -> barber cancelled
            // Életszerű: csak a booked-ot mondjuk le (completed-et általában nem piszkálunk)
            DB::table('appointments')
                ->where('barberId', $data['barberId'])
                ->where('appointmentDate', $data['offDay'])
                ->where('status', 'booked')
                ->update([
                    'status' => 'cancelled',
                    'cancelledBy' => 'barber',
                ]);
        });

        return response()->json([
            'message' => 'Szabadnap mentve. Az érintett foglalások lemondva.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(barberOffDay $barberOffDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatebarberOffDayRequest $request, barberOffDay $barberOffDay)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(barberOffDay $barberOffDay)
    {
        //
    }
}
