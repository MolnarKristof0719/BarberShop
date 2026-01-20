<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

      $fileName = 'csv/sports.csv';
        $delimiter = ';';

        $data = CsvReader::csvToArray($fileName, $delimiter);
        Sports::factory()->createMany($data);
    }
}
