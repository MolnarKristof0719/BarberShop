<?php

namespace App\Http\Controllers;

use App\Mail\BookingCreatedMail;
use App\Models\Appointment as CurrentModel;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    private const WORK_START = '09:00';
    private const WORK_END = '18:00';
    private const SLOT_MINUTES = 30;
    private const MIN_LEAD_MINUTES = 60;

    public function indexSortSearch($column, $direction, $search = null)
    {
        return $this->apiResponse(function () use ($column, $direction, $search) {
            CurrentModel::markElapsedAsCompleted();
            $query = CurrentModel::query();

            if (!empty($search) && $search !== 'all') {
                $query->where(function ($q) use ($search) {
                    $q->where('appointment', 'like', "%{$search}%");
                });
            }

            $allowedColumns = ['id', 'appointment', 'barberId', 'appointmentDate'];
            $sortColumn = in_array($column, $allowedColumns) ? $column : 'id';
            $sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

            return $query->orderBy($sortColumn, $sortDirection)->get();
        });
    }

    public function index()
    {
        return $this->apiResponse(function () {
            CurrentModel::markElapsedAsCompleted();
            $user = auth()->user();

            if ($user?->isBarber()) {
                $barberId = $user->barber?->id;
                abort_unless((bool) $barberId, 403);

                return CurrentModel::query()
                    ->where('barberId', $barberId)
                    ->orderByDesc('appointmentDate')
                    ->orderByDesc('appointmentTime')
                    ->with(['services', 'user'])
                    ->get();
            }

            if ($user?->isAdmin()) {
                return CurrentModel::query()
                    ->orderByDesc('appointmentDate')
                    ->orderByDesc('appointmentTime')
                    ->with(['services', 'user', 'barber'])
                    ->get();
            }

            abort_unless((bool) $user, 403);

            return CurrentModel::query()
                ->where('userId', $user->id)
                ->orderByDesc('appointmentDate')
                ->orderByDesc('appointmentTime')
                ->with(['services', 'barber'])
                ->get();
        });
    }

    public function store(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $validSlots = $this->generateSlots();
            $data = $request->validate([
                'barberId' => ['required', 'integer', 'exists:barbers,id'],
                'appointmentDate' => ['required', 'date'],
                'appointmentTime' => [
                    'required',
                    'date_format:H:i',
                    function (string $attribute, mixed $value, \Closure $fail) use ($validSlots): void {
                        if (!in_array($value, $validSlots, true)) {
                            $fail('Az időpont csak 30 perces sáv lehet 09:00 és 17:30 között.');
                        }
                    }
                ],
                'services' => ['required', 'array', 'min:1'],
                'services.*' => ['integer', 'exists:services,id'],
            ]);

            $appointmentDateTime = Carbon::parse($data['appointmentDate'] . ' ' . $data['appointmentTime']);
            if ($appointmentDateTime->lt($this->minBookableDateTime())) {
                abort(422, 'Az időpont legalább 1 órával későbbre foglalható.');
            }

            $isBarberActive = DB::table('barbers')
                ->where('id', $data['barberId'])
                ->where('isActive', true)
                ->exists();

            if (!$isBarberActive) {
                abort(422, 'A barber jelenleg nem aktív.');
            }

            $isOffDay = DB::table('barber_off_days')
                ->where('barberId', $data['barberId'])
                ->where('offDay', $data['appointmentDate'])
                ->exists();

            if ($isOffDay) {
                abort(422, 'A barber ezen a napon szabadságon van.');
            }

            try {
                return DB::transaction(function () use ($data) {
                    $totalPrice = Service::query()
                        ->whereIn('id', $data['services'])
                        ->sum('price');

                    $appointment = CurrentModel::create([
                        'barberId' => $data['barberId'],
                        'userId' => auth()->id(),
                        'appointmentDate' => $data['appointmentDate'],
                        'appointmentTime' => $data['appointmentTime'],
                        'totalPrice' => (int) $totalPrice,
                        'status' => 'booked',
                        'cancelledBy' => 'none',
                    ]);

                    $appointment->services()->sync($data['services']);
                    $appointment->load(['services', 'barber.user', 'user']);

                    Mail::to($appointment->user->email)->send(new BookingCreatedMail($appointment));

                    return $appointment;
                });
            } catch (QueryException $e) {
                $mysqlCode = $e->errorInfo[1] ?? null;

                if ($mysqlCode === 1062) {
                    abort(409, 'Ez az időpont már foglalt ennél a barbernél.');
                }

                throw $e;
            }
        });
    }

    public function availability(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $validated = $request->validate([
                'barberId' => ['required', 'integer', 'exists:barbers,id'],
                'date' => ['nullable', 'date'],
                'month' => ['nullable', 'date_format:Y-m'],
            ]);

            $barberId = (int) $validated['barberId'];
            $month = $validated['month'] ?? now()->format('Y-m');
            $selectedDate = $validated['date'] ?? now()->toDateString();

            $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $slots = $this->generateSlots();
            $today = now()->startOfDay();

            $bookedRows = CurrentModel::query()
                ->where('barberId', $barberId)
                ->whereIn('status', ['booked', 'completed'])
                ->whereBetween('appointmentDate', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get(['appointmentDate', 'appointmentTime']);

            $bookedByDate = [];
            foreach ($bookedRows as $row) {
                $dateKey = Carbon::parse($row->appointmentDate)->toDateString();
                $timeKey = substr((string) $row->appointmentTime, 0, 5);
                $bookedByDate[$dateKey][$timeKey] = true;
            }

            $offDayRows = DB::table('barber_off_days')
                ->where('barberId', $barberId)
                ->whereBetween('offDay', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->pluck('offDay')
                ->all();

            $offDaySet = [];
            foreach ($offDayRows as $offDay) {
                $offDaySet[(string) $offDay] = true;
            }

            $fullyBookedDates = [];
            $cursor = $monthStart->copy();
            while ($cursor->lte($monthEnd)) {
                $date = $cursor->toDateString();
                $bookedSet = $bookedByDate[$date] ?? [];
                if (!$this->hasAnyAvailableSlot($date, $slots, $bookedSet, $offDaySet, $today)) {
                    $fullyBookedDates[] = $date;
                }
                $cursor->addDay();
            }

            $selectedBookedSet = $bookedByDate[$selectedDate] ?? [];
            $selectedSlots = [];
            foreach ($slots as $time) {
                $selectedSlots[] = [
                    'time' => $time,
                    'available' => $this->isSlotAvailable($selectedDate, $time, $selectedBookedSet, $offDaySet, $today),
                ];
            }

            return [
                'barberId' => $barberId,
                'month' => $month,
                'date' => $selectedDate,
                'workingHours' => [
                    'start' => self::WORK_START,
                    'end' => self::WORK_END,
                    'intervalMinutes' => self::SLOT_MINUTES,
                ],
                'offDays' => array_keys($offDaySet),
                'fullyBookedDates' => $fullyBookedDates,
                'slots' => $selectedSlots,
            ];
        });
    }

    public function earliestOptions(Request $request)
    {
        return $this->apiResponse(function () use ($request) {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
                'daysAhead' => ['nullable', 'integer', 'min:1', 'max:90'],
                'serviceIds' => ['nullable', 'array'],
                'serviceIds.*' => ['integer', 'exists:services,id'],
            ]);

            $limit = (int) ($validated['limit'] ?? 8);
            $daysAhead = (int) ($validated['daysAhead'] ?? 30);
            $fromDate = now()->startOfDay();
            $toDate = $fromDate->copy()->addDays($daysAhead);
            $slots = $this->generateSlots();
            $today = now()->startOfDay();

            $barbers = Barber::query()
                ->where('isActive', true)
                ->with('user:id,name')
                ->get(['id', 'userId', 'profilePicture']);

            if ($barbers->isEmpty()) {
                return [];
            }

            $barberIds = $barbers->pluck('id')->all();
            $bookedRows = CurrentModel::query()
                ->whereIn('barberId', $barberIds)
                ->whereIn('status', ['booked', 'completed'])
                ->whereBetween('appointmentDate', [$fromDate->toDateString(), $toDate->toDateString()])
                ->get(['barberId', 'appointmentDate', 'appointmentTime']);

            $bookedLookup = [];
            foreach ($bookedRows as $row) {
                $bookedLookup[$row->barberId][Carbon::parse($row->appointmentDate)->toDateString()][substr((string) $row->appointmentTime, 0, 5)] = true;
            }

            $offDayRows = DB::table('barber_off_days')
                ->whereIn('barberId', $barberIds)
                ->whereBetween('offDay', [$fromDate->toDateString(), $toDate->toDateString()])
                ->get(['barberId', 'offDay']);

            $offDayLookup = [];
            foreach ($offDayRows as $row) {
                $offDayLookup[$row->barberId][(string) $row->offDay] = true;
            }

            $options = [];
            foreach ($barbers as $barber) {
                $cursor = $fromDate->copy();
                while ($cursor->lte($toDate)) {
                    $date = $cursor->toDateString();
                    $bookedSet = $bookedLookup[$barber->id][$date] ?? [];
                    $offDaySet = $offDayLookup[$barber->id] ?? [];

                    foreach ($slots as $time) {
                        if ($this->isSlotAvailable($date, $time, $bookedSet, $offDaySet, $today)) {
                            $options[] = [
                                'barberId' => (int) $barber->id,
                                'barberName' => (string) ($barber->user?->name ?? 'Ismeretlen barber'),
                                'profilePicture' => $barber->profilePicture,
                                'appointmentDate' => $date,
                                'appointmentTime' => $time,
                            ];
                        }
                    }

                    $cursor->addDay();
                }
            }

            usort($options, function (array $a, array $b): int {
                $left = $a['appointmentDate'] . ' ' . $a['appointmentTime'];
                $right = $b['appointmentDate'] . ' ' . $b['appointmentTime'];
                return strcmp($left, $right);
            });

            return array_slice($options, 0, $limit);
        });
    }

    public function show(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            CurrentModel::markElapsedAsCompleted();
            $appointment = CurrentModel::query()
                ->with(['services', 'barber', 'user'])
                ->find($id);

            abort_if(!$appointment, 404, 'Az időpont nem található.');

            return $appointment;
        });
    }

    public function destroy(int $id)
    {
        return $this->apiResponse(function () use ($id) {
            $appointment = CurrentModel::query()->with(['services', 'barber', 'user'])->findOrFail($id);
            $user = auth()->user();

            abort_unless(
                $user?->isAdmin() ||
                $appointment->userId === $user?->id ||
                ($user?->isBarber() && $user->barber?->id === $appointment->barberId),
                403
            );

            if ($appointment->status === 'completed') {
                abort(422, 'Teljesített időpont nem mondható le.');
            }

            if ($appointment->status !== 'cancelled') {
                $cancelledBy = $appointment->userId === $user?->id ? 'customer' : 'barber';

                $appointment->update([
                    'status' => 'cancelled',
                    'cancelledBy' => $cancelledBy,
                ]);

                $appointment->refresh()->load(['services', 'barber', 'user']);
            }

            return $appointment;
        });
    }

    private function generateSlots(): array
    {
        $slots = [];
        $time = Carbon::createFromFormat('H:i', self::WORK_START);
        $end = Carbon::createFromFormat('H:i', self::WORK_END);

        while ($time->lt($end)) {
            $slots[] = $time->format('H:i');
            $time->addMinutes(self::SLOT_MINUTES);
        }

        return $slots;
    }

    private function hasAnyAvailableSlot(
        string $date,
        array $slots,
        array $bookedSet,
        array $offDaySet,
        Carbon $today
    ): bool {
        foreach ($slots as $time) {
            if ($this->isSlotAvailable($date, $time, $bookedSet, $offDaySet, $today)) {
                return true;
            }
        }
        return false;
    }

    private function isSlotAvailable(
        string $date,
        string $time,
        array $bookedSet,
        array $offDaySet,
        Carbon $today
    ): bool {
        if (isset($offDaySet[$date])) {
            return false;
        }

        $dateCarbon = Carbon::parse($date)->startOfDay();
        if ($dateCarbon->lt($today)) {
            return false;
        }

        if (isset($bookedSet[$time])) {
            return false;
        }

        $slotDateTime = Carbon::parse($date . ' ' . $time);
        if ($slotDateTime->lt($this->minBookableDateTime())) {
            return false;
        }

        return true;
    }

    private function minBookableDateTime(): Carbon
    {
        return now()->addMinutes(self::MIN_LEAD_MINUTES);
    }
}
