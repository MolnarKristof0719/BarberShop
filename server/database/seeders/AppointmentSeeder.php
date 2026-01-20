<?php

namespace Database\Seeders;

use App\Helpers\TimeSlotService;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        /** @var TimeSlotService $slotService */
        $slotService = app(TimeSlotService::class);

        $start = '9:00';
        $end = '17:00';

        $slots = $slotService->generateHalfHourSlots($start, $end);

        $barberIds = Barber::query()->pluck('id')->all();
        $userIds = User::query()->pluck('id')->all();

        if (empty($barberIds) || empty($userIds)) {
            return;
        }

        $target = 200;  
        $created = 0;
        $attempts = 0;
        $maxAttempts = $target * 80; 

        while ($created < $target && $attempts < $maxAttempts) {
            $attempts++;

            $barberId = $barberIds[array_rand($barberIds)];
            $userId = $userIds[array_rand($userIds)];

            $date = Carbon::today()
                ->addDays(random_int(0, 60))
                ->toDateString();

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
