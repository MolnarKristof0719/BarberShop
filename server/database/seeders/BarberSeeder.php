<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BarberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barberUsers = User::where('role', 2)->get();
        foreach ($barberUsers as $user) {
            Barber::create([
                'userId' => $user->id,
                'profilePicture' => 'https://picsum.photos/seed/barber_'.$user->id.'/300/300',
                'introduction' => fake()->paragraph(3),
            ]);
        }
    }
}
