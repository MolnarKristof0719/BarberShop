<?php

namespace App\Http\Controllers;

use App\Models\BarberOffDay;
use Illuminate\Http\Request;

class BarberOffDayController extends Controller
{
    public function index()
    {
        $barberId = auth()->user()->barber->id;

        return BarberOffDay::query()
            ->where('barberId', $barberId)
            ->orderBy('offDay')
            ->get();
    }

    public function store(Request $request)
    {
        $barberId = auth()->user()->barber->id;

        $data = $request->validate([
            'offDay' => ['required', 'date'],
        ]);

        return BarberOffDay::create([
            'barberId' => $barberId,
            'offDay' => $data['offDay'],
        ]);
    }

    public function destroy(int $id)
    {
        $row = BarberOffDay::findOrFail($id);

        abort_unless($row->barberId === auth()->user()->barber->id, 403);

        $row->delete();

        return response()->noContent();
    }
}
