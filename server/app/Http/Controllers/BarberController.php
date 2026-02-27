<?php

namespace App\Http\Controllers;

use App\Models\Barber as CurrentModel;
use App\Http\Requests\StoreBarberRequest as StoreCurrentModelRequest;
use App\Http\Requests\UpdateBarberRequest as UpdateCurrentModelRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class BarberController extends Controller
{
    use AuthorizesRequests;

    public function indexAbc()
    {
        return $this->apiResponse(
            function () {
                return DB::table('barbers')
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
            }
        );
    }

    public function indexSortSearch($column, $direction, $search = null)
    {
        return $this->apiResponse(
            function () use ($column, $direction, $search) {

                $query = CurrentModel::query();

                // 2. Szűrés (ha van keresőszó)
                if (!empty($search) && $search !== 'all') {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                        // ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                // 3. Sorbarendezés
                $allowedColumns = ['id', 'name']; // Biztonsági lista
                $sortColumn = in_array($column, $allowedColumns) ? $column : 'id';
                $sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
                $rows = $query->orderBy($sortColumn, $sortDirection)->get();

                return $rows;
            }
        );
    }
    public function index()
    {
        return $this->apiResponse(function () {
            return CurrentModel::query()
                ->with(['user:id,name,email'])
                ->get();
        });
    }

    public function store(StoreCurrentModelRequest $request)
    {
        return $this->apiResponse(function () use ($request) {
            $this->authorizeAdmin();

            $data = $request->validated();

            return CurrentModel::create([
                'userId' => $data['userId'],
                'profilePicture' => $data['profilePicture'] ?? null,
                'introduction' => $data['introduction'] ?? null,
                'isActive' => $data['isActive'] ?? true,
            ]);
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            return CurrentModel::query()
                ->with(['referencePictures', 'reviews', 'user'])
                ->findOrFail($id);
        });
    }

    public function update(UpdateCurrentModelRequest $request, int $id)
    {
        return $this->apiResponse(function () use ($request, $id) {
            $barber = CurrentModel::findOrFail($id);

            $user = auth()->user();
            abort_unless(
                $user?->isAdmin() || ($user?->isBarber() && $user?->barber?->id === $barber->id),
                403
            );

            $data = $request->validated();

            $barber->fill([
                'profilePicture' => $data['profilePicture'] ?? $barber->profilePicture,
                'introduction' => $data['introduction'] ?? $barber->introduction,
            ]);

            if ($user?->isAdmin()) {
                if (array_key_exists('isActive', $data)) {
                    $barber->isActive = (bool) $data['isActive'];
                }
                if (array_key_exists('userId', $data)) {
                    $barber->userId = (int) $data['userId'];
                }
            }

            $barber->save();

            return $barber->fresh()->load(['referencePictures', 'reviews', 'user']);
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $this->authorizeAdmin();

            $barber = CurrentModel::findOrFail($id);
            $barber->delete();

            // Egységes "data" szerkezetet adunk vissza.
            // Ha ragaszkodsz a 204 No Content-hez, azt lentebb írom.
            return ['id' => $id];
        });
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
