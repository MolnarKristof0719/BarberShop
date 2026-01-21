<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::query()
            ->orderBy('service')
            ->get();
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'service' => ['required', 'string', 'max:255'],
        ]);

        return Service::create($data);
    }

    public function show(int $id)
    {
        return Service::findOrFail($id);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAdmin();

        $service = Service::findOrFail($id);

        $data = $request->validate([
            'service' => ['required', 'string', 'max:255'],
        ]);

        $service->update($data);

        return $service;
    }

    public function destroy(int $id)
    {
        $this->authorizeAdmin();

        $service = Service::findOrFail($id);
        $service->delete();

        return response()->noContent();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
