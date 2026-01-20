<?php

namespace Database\Seeders;

use App\Helpers\TimeSlotService;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        /** @var TimeSlotService $slotService */
        $slotService = app(TimeSlotService::class);

        $start = '09:00';
        $end   = '17:00';

        $slots = $slotService->generateHalfHourSlots($start, $end);

        $barberIds = Barber::query()->pluck('id')->all();
        $userIds   = User::query()->pluck('id')->all();

        if (empty($barberIds) || empty($userIds)) {
            return;
        }

        // csak a következő 2 hónapig generálunk dátumokat
        $maxDate = Carbon::today()->addMonthsNoOverflow(2);
        $maxDaysAhead = Carbon::today()->diffInDays($maxDate);

        $target = 200;
        $created = 0;
        $attempts = 0;
        $maxAttempts = $target * 120;

        while ($created < $target && $attempts < $maxAttempts) {
            $attempts++;

            $barberId = $barberIds[array_rand($barberIds)];
            $userId   = $userIds[array_rand($userIds)];

            // --- DÁTUM VÁLASZTÁS: 2 hónapon belül + ne legyen offDay ---
            $tries = 0;
            $maxTriesForDate = 40;

            do {
                $tries++;

                $date = Carbon::today()
                    ->addDays(random_int(0, $maxDaysAhead))
                    ->toDateString();

                $isOffDay = DB::table('barber_off_days')
                    ->where('barberId', $barberId)
                    ->where('offDay', $date)
                    ->exists();

            } while ($isOffDay && $tries < $maxTriesForDate);

            // ha nem találtunk nem-offDay dátumot (ritka), ugorjuk ezt a kört
            if ($isOffDay) {
                continue;
            }
            // --- /DÁTUM VÁLASZTÁS ---

            $time = $slots[array_rand($slots)];

            $isPast = Carbon::parse("$date $time")->isPast();

            $statusPool = $isPast
                ? ['completed', 'cancelled']
                : ['booked', 'cancelled'];

            $status = $statusPool[array_rand($statusPool)];

            $cancelledBy = 'none';
            if ($status === 'cancelled') {
                $cancelledBy = (random_int(0, 1) === 0) ? 'barber' : 'customer';
            }

            // UNIQUE-safe (barberId + date + time)
            $appointment = Appointment::firstOrCreate(
                [
                    'barberId' => $barberId,
                    'appointmentDate' => $date,
                    'appointmentTime' => $time,
                ],
                [
                    'userId' => $userId,
                    'status' => $status,
                    'cancelledBy' => $cancelledBy,
                ]
            );

            if ($appointment->wasRecentlyCreated) {
                $created++;
            }
        }
    }
}
