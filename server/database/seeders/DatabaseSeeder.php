<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //Mielőtt seedelünk, minden táblát töröljünk le.
        DB::statement('DELETE FROM users');
        DB::statement('DELETE FROM barbers');
        DB::statement('DELETE FROM appointments');
        DB::statement('DELETE FROM appointmentservices');
        DB::statement('DELETE FROM reviews');
        DB::statement('DELETE FROM referencepictures');
        DB::statement('DELETE FROM services');
        DB::statement('DELETE FROM barberoffdays');
        



        //Ami Seeder osztály itt fel van sorolva, annak lefut a run() metódusa
        $this->call([
            UserSeeder::class,
            BarberSeeder::class,
            AppointmentSeeder::class,
            AppointmentServiceSeeder::class,
            ReviewSeeder::class,
            ReferencePictureSeeder::class,
            ServiceSeeder::class,
            BarberOffDaySeeder::class,
        ]);
    }
}
