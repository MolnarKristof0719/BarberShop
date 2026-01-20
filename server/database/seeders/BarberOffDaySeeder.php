<?php

namespace Database\Seeders;

use App\Models\Barber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BarberOffDaySeeder extends Seeder
{
    public function run(): void
    {
        $barberIds = Barber::query()->pluck('id')->all();

        if (empty($barberIds)) {
            return;
        }

        // Milyen időablakban generáljunk szabadnapokat (következő 60 nap)
        $daysAhead = 60;

        // Barberenként hány szabadnap legyen ebben az időszakban
        $offDaysPerBarber = 6;

        $inserted = 0;

        foreach ($barberIds as $barberId) {
            $picked = [];

            $attempts = 0;
            $maxAttempts = $offDaysPerBarber * 50;

            while (count($picked) < $offDaysPerBarber && $attempts < $maxAttempts) {
                $attempts++;

                $date = Carbon::today()
                    ->addDays(random_int(0, $daysAhead))
                    ->toDateString();

                // ugyanazt a napot ne vegyük fel kétszer ennél a barbernél (seed szinten)
                if (isset($picked[$date])) {
                    continue;
                }

                // DB unique miatt safe
                $ok = DB::table('barber_off_days')->insertOrIgnore([
                    'barberId' => $barberId,
                    'offDay' => $date,
                ]);

                if ($ok === 1) {
                    $picked[$date] = true;
                    $inserted++;
                }
            }
        }

        $this->command?->info("Inserted barber_off_days rows: {$inserted}");
    }
}
