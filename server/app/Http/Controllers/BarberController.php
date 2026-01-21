<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Http\Requests\StoreBarberRequest;
use App\Http\Requests\UpdateBarberRequest;
use Illuminate\Support\Facades\DB;

class BarberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $rows = Barber::all();
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
    public function store(StoreBarberRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $row = Barber::find($id);
        if ($row) {
            $status = 200;
            $data = [
                'message' => 'OK',
                'data' => $row
            ];
        } else {
            $status = 404;
            $data = [
                'message' => "Not_Found id: $id ",
                'data' => null
            ];
        }

        return response()->json($data, $status, options: JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarberRequest $request, Barber $barber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barber $barber)
    {
        //
    }
}
