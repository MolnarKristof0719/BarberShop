<?php

namespace App\Http\Controllers;

use App\Models\BarberOffDay;
use Illuminate\Http\Request;

class BarberOffDayController extends Controller
{
    // Index: lekéri a szabadnapokat a bejelentkezett barberhez
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            // Admin: minden barber szabadnapját látja
            $offDays = BarberOffDay::orderBy('barberId')->orderBy('offDay')->get();
        } elseif ($user->isBarber()) {
            // Barber: csak a saját szabadnapjai
            $offDays = BarberOffDay::where('barberId', $user->id)
                ->orderBy('offDay')
                ->get();
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($offDays);
    }

    // Store: új szabadnap hozzáadása
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isBarber()) {
            return response()->json(['error' => 'Only barbers can add off days'], 403);
        }

        $data = $request->validate([
            'offDay' => ['required', 'date'],
        ]);

        $exists = BarberOffDay::where('barberId', $user->id)
            ->where('offDay', $data['offDay'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'This day is already marked as off'], 422);
        }

        $newDay = BarberOffDay::create([
            'barberId' => $user->id,
            'offDay' => $data['offDay'],
        ]);

        return response()->json($newDay, 201);
    }

    // Destroy: szabadnap törlése
    public function destroy(int $id)
    {
        $user = auth()->user();

        // Próbáljuk lekérni a rekordot, ha nincs → 404 JSON hiba
        $row = BarberOffDay::find($id);

        if (!$row) {
            return response()->json([
                'error' => 'Off day not found'
            ], 404);
        }

        // Csak a saját barber szabadnapját lehet törölni, admin bármelyiket
        if ($user->isBarber() && $row->barberId !== $user->id) {
            return response()->json([
                'error' => 'Forbidden: You cannot delete this off day'
            ], 403);
        }

        // Törlés
        $row->delete();

        return response()->json([
            'message' => 'Off day deleted successfully',
            'id' => $id
        ], 200);
    }
}
