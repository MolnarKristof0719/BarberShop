<?php

namespace Database\Seeders;

use App\Models\User;
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
            'password' => '123',
            'role' => 1
        ]);
        User::factory()->create([
            'name' => 'Barber',
            'email' => 'barber@example.com',
            'password' => '123',
            'role' => 2
        ]);
        User::factory()->create([
            'name' => 'Costumer',
            'email' => 'costumer1@example.com',
            'password' => '123',
            'role' => 3
        ]);
    }
}
