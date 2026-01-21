<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService;
use App\Http\Requests\StoreAppointmentServiceRequest;
use App\Http\Requests\UpdateAppointmentServiceRequest;

class AppointmentServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $rows = AppointmentService::all();
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
    public function store(StoreAppointmentServiceRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AppointmentService $appointmentService)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentServiceRequest $request, AppointmentService $appointmentService)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppointmentService $appointmentService)
    {
        //
    }
}
