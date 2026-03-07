<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite\Table;

trait DefaultValueTestTrait
{
    public function testDefaultValue(): void
    {
        $this->assertSame(
            'default',
            $this->schema
                ->table('test')
                ->column('text')
                ->defaultValue()
        );
    }

    public function testDefaultValueDecimal(): void
    {
        $this->assertSame(
            '2.5',
            $this->schema
                ->table('test')
                ->column('price')
                ->defaultValue()
        );
    }

    public function testDefaultValueExpression(): void
    {
        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
            $this->schema
                ->table('test')
                ->column('created')
                ->defaultValue()
        );
    }

    public function testDefaultValueInt(): void
    {
        $this->assertSame(
            5,
            $this->schema
                ->table('test')
                ->column('value')
                ->defaultValue()
        );
    }

    public function testDefaultValueNone(): void
    {
        $this->assertSame(
            '',
            $this->schema
                ->table('test_values')
                ->column('value')
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
