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
        'status' => 'enum',
        'cancelledBy' => 'enum',
    ];

    public static function expectedSchemaDataProvider(): array
    {
        return [
            'id oszlop' => ['id', 'bigint'],
            'barber id oszlop' => ['barberId', 'bigint'],
            'user id oszlop' => ['userId', 'bigint'],
            'appointment date oszlop' => ['appointmentDate', 'date'],
            'appointment time oszlop' => ['appointmentTime', 'time'],
            'status oszlop' => ['status', 'enum'],
            'cancelled by oszlop' => ['cancelledBy', 'enum'],
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
            "A '{$expectedColumn}' oszlop nem létezik"
        );
    }

    #[DataProvider('expectedSchemaDataProvider')]
    public function test_the_appointments_table_columns_have_the_expected_types(string $expectedColumn, string $expectedType): void
    {
        $actualType = Schema::getColumnType($this->table, $expectedColumn);

        $this->assertSame(
            $expectedType,
            $actualType,
            "A '{$expectedColumn}' oszlop típusa nem egyezik. Várt: '{$expectedType}', Kapott: '{$actualType}'."
        );
    }
}
