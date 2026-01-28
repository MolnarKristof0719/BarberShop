<?php

namespace App\Http\Controllers;

use App\Models\AppointmentService as CurrentModel;

class AppointmentServiceController extends Controller
{
    public function index()
    {
        return $this->apiResponse(function () {
            abort_unless(auth()->user()?->isAdmin(), 403);

            return CurrentModel::query()->get();
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            abort_unless(auth()->user()?->isAdmin(), 403);

            return CurrentModel::findOrFail($id);
        });
    }
}
