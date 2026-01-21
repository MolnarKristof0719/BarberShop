<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Http\Requests\StoreBarberRequest;
use App\Http\Requests\UpdateBarberRequest;

class BarberController extends Controller
{
    public function index()
    {
        return Barber::query()->get();
    }

    public function store(StoreBarberRequest $request)
    {
        $this->authorizeAdmin();

        $data = $request->validated();

        return Barber::create([
            'userId' => $data['userId'],
            'profilePicture' => $data['profilePicture'] ?? null,
            'introduction' => $data['introduction'] ?? null,
            'isActive' => $data['isActive'] ?? true,
        ]);
    }

    public function show(int $id)
    {
        return Barber::findOrFail($id);
    }

    public function update(UpdateBarberRequest $request, int $id)
    {
        $barber = Barber::findOrFail($id);

        // admin VAGY a saját barber profilja
        $user = auth()->user();
        abort_unless(
            $user?->isAdmin() || ($user?->isBarber() && $user?->barber?->id === $barber->id),
            403
        );

        $data = $request->validated();

        // bárki frissítheti a saját profil adatát
        $barber->fill([
            'profilePicture' => $data['profilePicture'] ?? $barber->profilePicture,
            'introduction' => $data['introduction'] ?? $barber->introduction,
        ]);

        // admin-only mezők (ha így akarjátok)
        if ($user?->isAdmin()) {
            if (array_key_exists('isActive', $data)) $barber->isActive = (bool)$data['isActive'];
            if (array_key_exists('userId', $data)) $barber->userId = (int)$data['userId'];
        }

        $barber->save();

        return $barber->fresh();
    }

    public function destroy(int $id)
    {
        $this->authorizeAdmin();

        $barber = Barber::findOrFail($id);
        $barber->delete();

        return response()->noContent();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
