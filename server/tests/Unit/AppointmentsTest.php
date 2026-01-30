<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AppointmentsTest extends TestCase
{
    protected $table = 'appointments';

    protected $expectedSchema = [
        'id' => 'bigint',
        'barberId' => 'bigint',
        'userId' => 'bigint',
        'appointmentDate' => 'date',
        'appointmentTime' => 'time',
        'status' => 'varchar',
        'cancelledBy' => 'varchar',
    ];

    public static function expectedSchemaDataProvider(): array
    {
        return [
            'id oszlop' => ['id', 'bigint'],
            'barberId oszlop' => ['barberId', 'bigint'],
            'userId oszlop' => ['userId', 'bigint'],
            'appointmentDate oszlop' => ['appointmentDate', 'date'],
            'appointmentTime oszlop' => ['appointmentTime', 'time'],
            'status oszlop' => ['status', 'varchar'],
            'cancelledBy oszlop' => ['cancelledBy', 'varchar'],
        ];
    }

    public function test_exists_appointments_table(): void
    {
        $this->assertTrue(
            Schema::hasTable($this->table),
            "Az appointments tábla nem létezik"
        );
    }

    #[DataProvider('expectedSchemaDataProvider')]
    public function test_does_the_appointments_table_contain_all_fields(string $expectedColumn, string $expectedType): void
    {
        $this->assertTrue(
            Schema::hasColumn($this->table, $expectedColumn),
            "A '{$expectedColumn}' oszlop nem letezik"
        );
    }

    #[DataProvider('expectedSchemaDataProvider')]
    public function test_the_appointments_table_columns_have_the_expected_types(string $expectedColumn, string $expectedType): void
    {
        $columns = Schema::getColumnListing($this->table);

        $this->assertEmpty(
            array_diff(array_keys($this->expectedSchema), $columns),
            'Hiányzó oszlopok az appointments táblában.'
        );

        foreach ($this->expectedSchema as $expectedColumn => $expectedType) {
            $actualDbSqlType = Schema::getColumnType($this->table, $expectedColumn);

            $this->assertTrue(
                $actualDbSqlType == $expectedType,
                "A '{$expectedColumn}' oszlop típusa nem egyezik. Várt: '{$expectedType}', Kapott DB-típus: '{$actualDbSqlType}'."
            );
        }
    }
}
