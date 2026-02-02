<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReviewsTest extends TestCase
{
   
    protected $table = 'reviews';
    public static function expectedSchemaDataProvider()
    {
        return [
            'id oszlop' => ['id', 'bigint'],
            'appointmentId oszlop' => ['appointmentId', 'bigint'],
            'barberId oszlop' => ['barberId', 'bigint'],
            'userId oszlop' => ['userId', 'bigint'],
            'rating oszlop' => ['rating', 'int'],
            'comment oszlop' => ['comment', 'text'],

        ];
    }

    public function test_exists_reviews_table(): void
    {
        //Ellenőrizze, hogy megvan-e a tábla

        $this->assertTrue(Schema::hasTable($this->table), "A barbers tábla nem létezik");
    }

    #[DataProvider('expectedSchemaDataProvider')]
    public function test_does_the_reviews_table_contain_all_fields(string $expectedColumn, string $expectedType): void
    {
        //Ellenőrizze, hogy megvannak-e a tábla mezői
        $this->assertTrue(Schema::hasColumn($this->table, $expectedColumn), "A '$expectedColumn' oszlop nem letezik");
    }

    #[DataProvider('expectedSchemaDataProvider')]
    public function test_the_reviews_table_columns_have_the_expected_types($expectedColumn, $expectedType)
    {
        //Ellenőrizze, hogy jók-e a típusai

        $actualDbSqlType = Schema::getColumnType($this->table, $expectedColumn);

        
        $isTypeMatch = $actualDbSqlType == $expectedType;
        $this->assertTrue(
            $isTypeMatch,
            "A '{$expectedColumn}' oszlop típusa nem egyezik. Várt: '{$expectedType}', Kapott DB-típus: '{$actualDbSqlType}'."
        );
    }

}
