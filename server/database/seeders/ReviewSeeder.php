<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('hu_HU');

        // Csak completed appointmentekhez
        $appointments = Appointment::query()
            ->where('status', 'completed')
            ->get();

        if ($appointments->isEmpty()) {
            $this->command?->warn('No completed appointments found. No reviews created.');
            return;
        }

        $inserted = 0;

        foreach ($appointments as $appointment) {

            // opcionális: nem mindenki ír review-t (ha mindet akarod, töröld ezt az if-et)
            if (random_int(1, 100) > 70) {
                continue;
            }

            $rating = random_int(1, 5);
            $comment = $faker->sentence(12);

            $ok = DB::table('reviews')->insertOrIgnore([
                'appointmentId' => $appointment->id,
                'barberId'      => $appointment->barberId,
                'userId'        => $appointment->userId,
                'rating'        => $rating,
                'comment'       => $comment,
            ]);

            if ($ok === 1) {
                $inserted++;
            }
        }

        $this->command?->info("Inserted reviews rows: {$inserted}");
    }
}
