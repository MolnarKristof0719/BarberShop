<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentServiceSeeder extends Seeder
{
    public function run(): void
    {
        $appointmentIds = Appointment::query()->pluck('id')->all();
        $serviceIds     = Service::query()->pluck('id')->all();

        if (empty($appointmentIds) || empty($serviceIds)) {
            return;
        }

        foreach ($appointmentIds as $appointmentId) {

            // 1–3 service egy foglaláshoz
            $serviceCount = random_int(1, min(3, count($serviceIds)));

            // véletlen, de UNIQUE service id-k
            $selectedServiceIds = collect($serviceIds)
                ->shuffle()
                ->take($serviceCount);

            foreach ($selectedServiceIds as $serviceId) {
                DB::table('appointment_services')->insertOrIgnore([
                    'appointmentId' => $appointmentId,
                    'serviceId' => $serviceId,
                ]);
            }
        }
    }
}
