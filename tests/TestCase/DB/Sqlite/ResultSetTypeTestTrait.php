<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use Fyre\DB\Type;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
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
            'boolean' => ['CAST(1 AS BOOLEAN)', IntegerType::class],
            'date' => ['DATE()', StringType::class],
            'double' => ['CAST(1 AS DOUBLE PRECISION)', FloatType::class],
            'integer' => ['CAST(2147483647 AS INTEGER)', IntegerType::class],
            'numeric' => ['CAST(1 AS NUMERIC)', IntegerType::class],
            'real' => ['CAST(1 AS REAL)', FloatType::class],
            'smallint' => ['CAST(32767 AS SMALLINT)', IntegerType::class],
            'time' => ['TIME()', StringType::class],
            'timestamp' => ['CURRENT_TIMESTAMP', StringType::class],
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
