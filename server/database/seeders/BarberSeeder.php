<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;

class BarberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scrappyCocoProfilePicture = 'IDE_IRD_A_SAJAT_LINKED';

        $barberUsers = User::where('role', 2)->get();
        foreach ($barberUsers as $user) {
            Barber::create([
                'userId' => $user->id,
                'profilePicture' => match ($user->email) {
                    'arabpacek@example.com' => 'https://entertainment.time.com/wp-content/uploads/sites/3/2012/04/the-dictator.jpg?w=600',
                    's.coco@example.com' => 'https://pbs.twimg.com/profile_images/1164039502517506048/Ns7MBZEG_400x400.jpg',
                    default => 'https://xsgames.co/randomusers/assets/avatars/male/' . $user->id . '.jpg',
                },
                'introduction' => $user->email === 'arabpacek@example.com'
                    ? 'أرسلت الطرد.'
                    : fake()->paragraph(3),
                'isActive' => fake()->boolean(95),
            ]);
        }
    }
}
