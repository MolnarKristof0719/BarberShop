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

        $slots = $slotService->generateHalfHourSlots('09:00', '17:00');

        $barberIds = Barber::query()->pluck('id')->all();
        $userIds   = User::query()->pluck('id')->all();

        if (empty($barberIds) || empty($userIds)) {
            return;
        }

        // csak a következő 2 hónapig generálunk dátumokat
        $maxDate = Carbon::today()->addMonthsNoOverflow(2);
        $maxDaysAhead = Carbon::today()->diffInDays($maxDate);

        // customer lemondás esélye (%)
        $cancelChancePast = 10;   // múltban 10% cancelled (customer)
        $cancelChanceFuture = 5;  // jövőben 5% cancelled (customer)

        // offDay-re mennyi eséllyel csináljunk rekordot? (%)
        // (ha 0, akkor offDay-re sosem generál)
        $offDayGenerateChance = 30;

        $target = 200;
        $created = 0;
        $attempts = 0;
        $maxAttempts = $target * 150;

        while ($created < $target && $attempts < $maxAttempts) {
            $attempts++;

            $barberId = $barberIds[array_rand($barberIds)];
            $userId   = $userIds[array_rand($userIds)];

            $date = Carbon::today()
                ->addDays(random_int(0, $maxDaysAhead))
                ->toDateString();

            $time = $slots[array_rand($slots)];

            $isOffDay = DB::table('barber_off_days')
                ->where('barberId', $barberId)
                ->where('offDay', $date)
                ->exists();

            $isPast = Carbon::parse("$date $time")->isPast();

            // --- STÁTUSZ LOGIKA ---
            if ($isOffDay) {
                // offDay-re csak akkor generálunk, ha belefér a chance-be
                if (random_int(1, 100) > $offDayGenerateChance) {
                    continue; // ugorjuk, ne legyen rekord erre a körre
                }

                // offDay => barber cancelled
                $status = 'cancelled';
                $cancelledBy = 'barber';
            } else {
                // nem offDay => customer néha lemondja
                $shouldCancelByCustomer = $isPast
                    ? random_int(1, 100) <= $cancelChancePast
                    : random_int(1, 100) <= $cancelChanceFuture;

                if ($shouldCancelByCustomer) {
                    $status = 'cancelled';
                    $cancelledBy = 'customer';
                } else {
                    $status = $isPast ? 'completed' : 'booked';
                    $cancelledBy = 'none';
                }
            }
            // --- /STÁTUSZ LOGIKA ---

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
