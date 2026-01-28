<?php

namespace App\Http\Controllers;

use App\Models\BarberOffDay as CurrentModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BarberOffDayController extends Controller
{
    // Index: admin -> mindet, barber -> saját (barbers.id alapján)
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

    // Store: csak barber adhat hozzá off day-t (barbers.id)
    public function store(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $user = auth()->user();

            if ($user?->isAdmin()) {
                throw new HttpException(403, 'Adminként nem adhatsz hozzá szabadnapot.');
            }

            if (!$user?->isBarber()) {
                throw new HttpException(403, 'Csak borbély adhat hozzá szabadnapot.');
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
                throw new HttpException(422, 'Ez a nap már szabadnapként szerepel.');
            }

            return CurrentModel::create([
                'barberId' => $barberId,
                'offDay' => $data['offDay'],
            ]);
        });
    }


    // Destroy: admin törölhet bármit, barber csak a sajátját (barbers.id)
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

            $row->delete();

            return ['id' => $id];
        });
    }
}
