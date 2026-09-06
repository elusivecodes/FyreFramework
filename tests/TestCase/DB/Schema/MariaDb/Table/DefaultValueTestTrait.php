<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\MariaDb\Table;

use PHPUnit\Framework\Attributes\DataProvider;

trait DefaultValueTestTrait
{
    /**
     * @return array<string, array{int|string, string, string}>
     */
    public static function defaultValueProvider(): array
    {
        return [
            'default' => ['default', 'test', 'text'],
            'decimal' => ['2.5', 'test', 'price'],
            'int' => [5, 'test', 'value'],
            'none' => ['', 'test_values', 'value'],
        ];
    }

    #[DataProvider('defaultValueProvider')]
    public function testDefaultValue(int|string $expected, string $table, string $column): void
    {
        $this->assertSame(
            $expected,
            $this->schema
                ->table($table)
                ->column($column)
                ->defaultValue()
        );
    }

    public function testDefaultValueExpression(): void
    {
        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[+-]\d{2}(?::?\d{2})?)?\z/',
            $this->schema
                ->table('test')
                ->column('created')
                ->defaultValue()
        );
    }

    public function testDefaultValueNull(): void
    {
        $this->assertNull(
            $this->schema
                ->table('test')
                ->column('name')
                ->defaultValue()
        );
    }
}
