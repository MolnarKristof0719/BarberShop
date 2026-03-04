<?php

namespace Database\Seeders;

use App\Models\Barber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferencePictureSeeder extends Seeder
{
    public function run(): void
    {
        $barberIds = Barber::query()->pluck('id')->all();

        if (empty($barberIds)) {
            return;
        }

        $inserted = 0;
        $now = now();
        $tags = [
            'barber,haircut',
            'barbershop,hair',
            'barber,fade',
            'barber,beard',
            'men,haircut',
        ];

        foreach ($barberIds as $barberId) {
            // Barberenkent 6-12 referencia kep
            $count = random_int(6, 12);

            for ($i = 1; $i <= $count; $i++) {
                $tagIndex = ($barberId + $i) % count($tags);
                $tag = $tags[$tagIndex];
                $lockId = ($barberId * 1000) + $i;

                // Egyedi URL-ek: lock alapjan mindegyik sor kulonbozo lesz.
                $picture = "https://loremflickr.com/640/480/{$tag}?lock={$lockId}";

                $inserted += DB::table('reference_pictures')->insertOrIgnore([
                    'barberId' => $barberId,
                    'picture' => $picture,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command?->info("Inserted reference_pictures rows: {$inserted}");
    }
}
