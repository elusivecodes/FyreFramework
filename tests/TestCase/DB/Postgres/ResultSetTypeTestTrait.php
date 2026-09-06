<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres;

use Fyre\DB\Type;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\TimeType;
use PHPUnit\Framework\Attributes\DataProvider;

trait ResultSetTypeTestTrait
{
    /**
     * @return array<string, array{string, class-string<Type>}>
     */
    public static function typeVirtualFieldProvider(): array
    {
        return [
            'bigint' => ['CAST(9223372036854775807 AS BIGINT)', IntegerType::class],
            'boolean' => ['CAST(1 AS BOOLEAN)', BooleanType::class],
            'date' => ['CAST(LOCALTIMESTAMP(0) AS DATE)', DateType::class],
            'double' => ['CAST(1 AS DOUBLE PRECISION)', FloatType::class],
            'integer' => ['CAST(2147483647 AS INTEGER)', IntegerType::class],
            'money' => ['CAST(1 AS MONEY)', DecimalType::class],
            'numeric' => ['CAST(1 AS NUMERIC)', DecimalType::class],
            'real' => ['CAST(1 AS REAL)', FloatType::class],
            'smallint' => ['CAST(32767 AS SMALLINT)', IntegerType::class],
            'time' => ['LOCALTIME(0)', TimeType::class],
            'timestamp' => ['LOCALTIMESTAMP(0)', DateTimeType::class],
            'timestamp with time zone' => ['CURRENT_TIMESTAMP(0)', DateTimeTimeZoneType::class],
        ];
    }

    /**
     * @param class-string<Type> $expected
     */
    #[DataProvider('typeVirtualFieldProvider')]
    public function testTypeVirtualField(string $expression, string $expected): void
    {
        $result = $this->db->select([
            'value' => $expression,
        ])
            ->execute();

        $this->assertInstanceOf(
            $expected,
            $result->getType('value')
        );
    }
}
