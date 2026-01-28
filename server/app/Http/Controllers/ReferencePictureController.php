<?php

namespace App\Http\Controllers;

use App\Models\ReferencePicture as CurrentModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReferencePictureController extends Controller
{
    public function index(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $query = CurrentModel::query();

            // Opcionális szűrés: /reference-pictures?barberId=1
            if ($request->filled('barberId')) {
                $query->where('barberId', (int) $request->barberId);
            }

            return $query->get();
        });
    }

    public function store(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $user = auth()->user();

            if (!$user?->isAdmin() && !$user?->isBarber()) {
                throw new HttpException(403, 'Nincs jogosultságod referenciaképet feltölteni.');
            }

            $data = $request->validate([
                // admin tölthet fel más barbernek is
                'barberId' => ['nullable', 'integer', 'exists:barbers,id'],
                'picture' => ['required', 'string', 'max:255'],
            ]);

            if ($user?->isAdmin()) {
                $barberId = (int) ($data['barberId'] ?? 0);

                if ($barberId <= 0) {
                    throw new HttpException(422, 'barberId kötelező admin feltöltésnél.');
                }
            } else {
                $barberId = $user->barber?->id;
                if (!$barberId) {
                    throw new HttpException(403, 'Barber profil nem található ehhez a felhasználóhoz.');
                }
            }

            return CurrentModel::create([
                'barberId' => $barberId,
                'picture' => $data['picture'],
            ]);
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            return CurrentModel::findOrFail($id);
        });
    }

    public function update(Request $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $row = CurrentModel::findOrFail($id);

            $user = auth()->user();
            if (
                !$user?->isAdmin() &&
                !($user?->isBarber() && $user->barber?->id === $row->barberId)
            ) {
                throw new HttpException(403, 'Nincs jogosultságod a referenciakép módosításához.');
            }

            $data = $request->validate([
                'picture' => ['required', 'string', 'max:255'],
            ]);

            $row->update([
                'picture' => $data['picture'],
            ]);

            return $row->fresh();
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $row = CurrentModel::findOrFail($id);

            $user = auth()->user();
            if (
                !$user?->isAdmin() &&
                !($user?->isBarber() && $user->barber?->id === $row->barberId)
            ) {
                throw new HttpException(403, 'Nincs jogosultságod a referenciakép törléséhez.');
            }

            $row->delete();

            // apiResponse egységes JSON-t ad vissza
            return ['id' => $id];
        });
    }
}
