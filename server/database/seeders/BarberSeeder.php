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
                'profilePicture' => $user->email === 'arabpacek@example.com'
                    ? 'https://entertainment.time.com/wp-content/uploads/sites/3/2012/04/the-dictator.jpg?w=600'
                    : 'https://xsgames.co/randomusers/assets/avatars/male/' . $user->id . '.jpg',
                'introduction' => $user->email === 'arabpacek@example.com'
                    ? 'أنا حلاق محترف متخصص في التدرجات الدقيقة وتشذيب اللحية بأعلى جودة.'
                    : fake()->paragraph(3),
                'isActive' => fake()->boolean(95),
            ]);
        }
    }
}
