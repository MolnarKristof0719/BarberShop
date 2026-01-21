<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService;

class AppointmentServiceController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return AppointmentService::query()->get();
    }

    public function show(int $id)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return AppointmentService::findOrFail($id);
    }
}
