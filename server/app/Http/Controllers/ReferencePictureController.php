<?php

namespace App\Http\Controllers;

use App\Models\ReferencePicture;
use Illuminate\Http\Request;

class ReferencePictureController extends Controller
{
    public function index(Request $request)
    {
        // Opcionális szűrés: /reference-pictures?barberId=1
        $query = ReferencePicture::query();

        if ($request->filled('barberId')) {
            $query->where('barberId', (int) $request->barberId);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        // barber vagy admin
        $user = auth()->user();
        abort_unless($user?->isAdmin() || $user?->isBarber(), 403);

        $data = $request->validate([
            // ha admin is tölthet fel más barbernek:
            'barberId' => ['nullable', 'integer', 'exists:barbers,id'],
            // ezt igazítsd a mezőnevetekhez (imageUrl / path / url):
            'imageUrl' => ['required', 'string', 'max:255'],
        ]);

        $barberId = $user->isAdmin()
            ? (int) ($data['barberId'] ?? 0)
            : $user->barber->id;

        // admin esetén barberId kötelező
        if ($user->isAdmin() && empty($barberId)) {
            return response()->json([
                'message' => 'barberId kötelező admin feltöltésnél.',
                'data' => null
            ], 422, options: JSON_UNESCAPED_UNICODE);
        }

        return ReferencePicture::create([
            'barberId' => $barberId,
            'imageUrl' => $data['imageUrl'],
        ]);
    }

    public function show(int $id)
    {
        return ReferencePicture::findOrFail($id);
    }

    public function update(Request $request, int $id)
    {
        $row = ReferencePicture::findOrFail($id);

        $user = auth()->user();
        abort_unless(
            $user?->isAdmin() ||
            ($user?->isBarber() && $user->barber?->id === $row->barberId),
            403
        );

        $data = $request->validate([
            'imageUrl' => ['required', 'string', 'max:255'],
        ]);

        $row->update([
            'imageUrl' => $data['imageUrl'],
        ]);

        return $row;
    }

    public function destroy(int $id)
    {
        $row = ReferencePicture::findOrFail($id);

        $user = auth()->user();
        abort_unless(
            $user?->isAdmin() ||
            ($user?->isBarber() && $user->barber?->id === $row->barberId),
            403
        );

        $row->delete();

        return response()->noContent();
    }
}
