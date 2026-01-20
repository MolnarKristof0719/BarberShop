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

        foreach ($barberIds as $barberId) {

            // barberenként 1–6 referencia kép
            $count = random_int(1, 6);

            for ($i = 1; $i <= $count; $i++) {

                /**
                 * Picsum seed:
                 * - barberId + i → garantáltan más kép
                 * - ugyanaz a barber nem kap kétszer ugyanazt
                 */
                $seed = 'barber_' . $barberId . '_' . $i;

                $picture = "https://picsum.photos/seed/{$seed}/600/600";

                $inserted += DB::table('reference_pictures')->insertOrIgnore([
                    'barberId' => $barberId,
                    'picture' => $picture,
                ]);
            }
        }

        $this->command?->info("Inserted reference_pictures rows: {$inserted}");
    }
}
