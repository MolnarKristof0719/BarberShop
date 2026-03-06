<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service as CurrentModel;

class ServiceController extends Controller
{
    public function indexSortSearch($column, $direction, $search = null)
    {
        return $this->apiResponse(function () use ($column, $direction, $search) {
            $query = CurrentModel::query();

            if (!empty($search) && $search !== 'all') {
                $query->where(function ($q) use ($search) {
                    $q->where('service', 'like', "%{$search}%");
                });
            }

            $allowedColumns = ['id', 'service'];
            $sortColumn = in_array($column, $allowedColumns) ? $column : 'id';
            $sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

            return $query->orderBy($sortColumn, $sortDirection)->get();
        });
    }

    //region index
    public function index()
    {
        return $this->apiResponse(function () {
            return CurrentModel::query()
                ->orderBy('service')
                ->get();
        });
    }
    //endregion

    //region store
    public function store(StoreServiceRequest $request)
    {
        return $this->apiResponse(function () use ($request) {
            $this->authorizeAdmin();

            $data = $request->validated();

            return CurrentModel::create($data);
        });
    }
    //endregion

    //region show
    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            // Stabil 404 üzenet (ne findOrFail, mert néha üres message-t ad)
            $row = CurrentModel::query()->find($id);
            abort_if(!$row, 404, 'Service not found');

            return $row;
        });
    }
    //endregion

    //region update
    public function update(UpdateServiceRequest $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $this->authorizeAdmin();

            $row = CurrentModel::query()->find($id);
            abort_if(!$row, 404, 'Service not found');

            $data = $request->validated();

            $row->update($data);

            return $row->fresh();
        });
    }
    //endregion

    //region destroy
    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $this->authorizeAdmin();

            $row = CurrentModel::query()->find($id);
            abort_if(!$row, 404, 'Service not found');

            $row->delete();

            // apiResponse miatt egységes JSON (nem 204)
            return ['id' => $id];
        });
    }
    //endregion

    //region authorizeAdmin
    private function authorizeAdmin(): void
    {
        // Ha szeretnél szöveges üzenetet is:
        // abort_unless(auth()->user()?->isAdmin(), 403, 'Admin jogosultság szükséges.');
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
    //endregion
}
