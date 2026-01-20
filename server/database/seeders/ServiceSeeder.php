<?php

namespace Database\Seeders;


use App\Helpers\CsvReader;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {

    $fileName = 'csv/services.csv';
    $delimiter = ';';

    $data = CsvReader::csvToArray($fileName, $delimiter);
    Service::factory()->createMany($data);
  }
}
