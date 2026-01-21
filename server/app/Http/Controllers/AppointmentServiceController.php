<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService;

class AppointmentServiceController extends Controller
{
    //region index
    public function index()
    {
        // admin-only, mert ez belső adat
        abort_unless(auth()->user()?->isAdmin(), 403);

        return AppointmentService::query()->get();
    }
    //endregion

    //region show
    public function show(int $id)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return AppointmentService::findOrFail($id);
    }
    //endregion
}
