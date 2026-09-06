<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql;

use Fyre\DB\Type;
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
            'bigint' => ['CAST(18446744073709551615 AS UNSIGNED INT)', IntegerType::class],
            'date' => ['CAST(LOCALTIMESTAMP() AS DATE)', DateType::class],
            'decimal' => ['CAST(1 AS DECIMAL)', DecimalType::class],
            'double' => ['CAST(1 AS DOUBLE)', FloatType::class],
            'float' => ['CAST(1 AS FLOAT)', FloatType::class],
            'integer' => ['CAST(4294967295 AS UNSIGNED INT)', IntegerType::class],
            'mediumint' => ['CAST(16777215 AS UNSIGNED INT)', IntegerType::class],
            'smallint' => ['CAST(65535 AS UNSIGNED INT)', IntegerType::class],
            'tinyint' => ['CAST(255 AS UNSIGNED INT)', IntegerType::class],
            'time' => ['CAST(LOCALTIMESTAMP() AS TIME)', TimeType::class],
            'timestamp' => ['LOCALTIMESTAMP()', DateTimeType::class],
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
