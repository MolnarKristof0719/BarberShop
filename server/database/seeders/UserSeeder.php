<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
            'phoneNumber' => null,
            'role' => 1
        ]);
        
        User::factory()->create([
            'name' => 'Barber',
            'email' => 'barber@example.com',
            'password' => Hash::make('123'),
            'phoneNumber' => fake()->phoneNumber(),
            'role' => 2
        ]);

        User::factory()->create([
            'name' => 'Customer',
            'email' => 'kristofm500@gmail.com',
            'password' => Hash::make('123'),
            'phoneNumber' => fake()->phoneNumber(),
            'role' => 3
        ]);

        User::factory()->create([
            'name' => 'Customer2',
            'email' => 'customer2@example.com',
            'password' => Hash::make('123'),
            'phoneNumber' => fake()->phoneNumber(),
            'role' => 3
        ]);

        User::factory()->count(5)->create([
            'role' => 2,
        ]);

        // 5 Customer
        User::factory()->count(5)->create([
            'role' => 3,
        ]);

    }
}
