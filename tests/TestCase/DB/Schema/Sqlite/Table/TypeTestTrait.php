<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite\Table;

use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\StringType;
use PHPUnit\Framework\Attributes\DataProvider;

trait TypeTestTrait
{
    /**
     * @return array<string, array{class-string<BooleanType>|class-string<DateTimeType>|class-string<DecimalType>|class-string<StringType>, string}>
     */
    public static function getTypeProvider(): array
    {
        return [
            'default' => [StringType::class, 'name'],
            'boolean' => [BooleanType::class, 'bool'],
            'date time' => [DateTimeType::class, 'created'],
            'decimal' => [DecimalType::class, 'price'],
        ];
    }

    /**
     * @param class-string<BooleanType>|class-string<DateTimeType>|class-string<DecimalType>|class-string<StringType> $expected
     */
    #[DataProvider('getTypeProvider')]
    public function testGetType(string $expected, string $column): void
    {
        $this->assertInstanceOf(
            $expected,
            $this->schema->table('test')
                ->column($column)
                ->type()
        );
    }
}
