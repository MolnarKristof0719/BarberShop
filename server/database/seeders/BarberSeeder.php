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
                'profilePicture' => 'https://xsgames.co/randomusers/assets/avatars/male/' . $user->id .'.jpg',
                'introduction' => fake()->paragraph(3),
                'isActive' => fake()->boolean(80), 
            ]);
        }
    }
}
