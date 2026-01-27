<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_database_creation_end_tables_exists()
    {
        $databaseNameConn = DB::connection()->getDatabaseName();
        $databaseNameEnv = env('DB_DATABASE');
        $this->assertEquals($databaseNameConn, $databaseNameEnv, "Az adatbázisunk nem az, amivel dolgozni akarunk");

        //Megvannak-e a tábláink
        $this->assertTrue(Schema::hasTable('services'), 'services tábla nem létezik');
        $this->assertTrue(Schema::hasTable('barbers'), 'barbers tábla nem létezik');
        $this->assertTrue(Schema::hasTable('appointments'), 'appointments tábla nem létezik');
        $this->assertTrue(Schema::hasTable('barber_off_days'), 'barber_off_days tábla nem létezik');
        $this->assertTrue(Schema::hasTable('reference_pictures'), 'reference_pictures tábla nem létezik');
        $this->assertTrue(Schema::hasTable('reviews'), 'reviews tábla nem létezik');
        $this->assertTrue(Schema::hasTable('users'), 'users tábla nem létezik');
        echo PHP_EOL . "Adatbázis -> DB_DATABASE={$databaseNameEnv} | adatbázis: {$databaseNameConn}";
    }

    public function test_services_table_structure()
    {
        // Ellenőrizzük, hogy a tábla létezik
        $this->assertTrue(Schema::hasTable('services'));

        // Ellenőrizzük a mezőket és azok típusait
        $this->assertTrue(Schema::hasColumn('services', 'id'));
        $this->assertTrue(Schema::hasColumn('services', 'service'));
        $this->assertEquals('bigint', Schema::getColumnType('services', 'id'), "services: Nem bigint az id típusa");
        //dd(Schema::getColumnType('sports', 'sportNev'));
        $this->assertEquals('varchar', Schema::getColumnType('services', 'service'));

        //Elsődleges kulcs
        $indexes = DB::select('SHOW INDEX FROM services');
        $primaryIndex = collect($indexes)->firstWhere('Key_name', 'PRIMARY');
        $this->assertNotNull($primaryIndex, "Nincs elsődleges kulcs");

        //Egyedi index: sportNev
        $uniqueIndex = collect($indexes)->firstWhere('Key_name', 'services_service_unique');

        $this->assertNotNull($uniqueIndex, "Hiányzik az egyedi index a 'services.service' oszlopon.");
    }

    public function test_barbers_relationships()
    {
        //A diák tábla kapcsolatai
        $tableName = "barber_off_days";
        $foreignKeyName = "barberId";
        $databaseName = env('DB_DATABASE');
        $contstraint_name = "PRIMARY";

        $query = "
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                information_schema.KEY_COLUMN_USAGE
            WHERE
                TABLE_NAME = ? and COLUMN_NAME = ? and CONSTRAINT_SCHEMA = ? and CONSTRAINT_NAME <> ?";

        $rows = collect(DB::select($query, [$tableName, $foreignKeyName, $databaseName, $contstraint_name]))
            ->firstWhere('REFERENCED_TABLE_NAME', 'barbers');

        $this->assertNotNull($rows, 'Nem található foreign key kapcsolat a barbers táblára');
        $this->assertEquals('barberId', $rows->COLUMN_NAME);
        $this->assertEquals('barbers', $rows->REFERENCED_TABLE_NAME);
        $this->assertEquals('id', $rows->REFERENCED_COLUMN_NAME);
    }
}