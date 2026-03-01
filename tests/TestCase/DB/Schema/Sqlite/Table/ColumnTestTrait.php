<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite\Table;

use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Column;
use Fyre\Utility\Collection;
use InvalidArgumentException;

trait ColumnTestTrait
{
    public function testColumn(): void
    {
        $column = $this->schema
            ->table('test')
            ->column('name');

        $this->assertInstanceOf(Column::class, $column);

        $this->assertSame(
            'name',
            $column->getName()
        );

        $this->assertSame(
            'varchar',
            $column->getType()
        );

        $this->assertSame(
            255,
            $column->getLength()
        );

        $this->assertNull(
            $column->getPrecision()
        );

        $this->assertNull(
            $column->getScale()
        );

        $this->assertNull(
            $column->getFractionalSeconds()
        );

        $this->assertTrue(
            $column->isNullable()
        );

        $this->assertFalse(
            $column->isUnsigned()
        );

        $this->assertSame(
            null,
            $column->getDefault()
        );

        $this->assertNull(
            $column->getComment()
        );

        $this->assertFalse(
            $column->isAutoIncrement()
        );
    }

    public function testColumnInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table column `test.invalid` does not exist.');

        $this->schema
            ->table('test')
            ->column('invalid');
    }

    public function testColumnNames(): void
    {
        $this->assertSame(
            [
                'id',
                'name',
                'value',
                'price',
                'text',
                'bool',
                'created',
                'modified',
            ],
            $this->schema->table('test')
                ->columnNames()
        );
    }

    public function testColumns(): void
    {
        $columns = $this->schema
            ->table('test')
            ->columns();

        $this->assertInstanceOf(Collection::class, $columns);

        $this->assertEquals(
            [
                'id' => [
                    'name' => 'id',
                    'type' => 'integer',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => null,
                    'comment' => null,
                    'autoIncrement' => true,
                ],
                'name' => [
                    'name' => 'name',
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'value' => [
                    'name' => 'value',
                    'type' => 'integer',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 5,
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'price' => [
                    'name' => 'price',
                    'type' => 'numeric',
                    'length' => null,
                    'precision' => 10,
                    'scale' => 2,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 2.5,
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'text' => [
                    'name' => 'text',
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => 'default',
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'bool' => [
                    'name' => 'bool',
                    'type' => 'boolean',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => false,
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'created' => [
                    'name' => 'created',
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => new QueryLiteral('CURRENT_TIMESTAMP'),
                    'comment' => null,
                    'autoIncrement' => false,
                ],
                'modified' => [
                    'name' => 'modified',
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => new QueryLiteral('CURRENT_TIMESTAMP'),
                    'comment' => null,
                    'autoIncrement' => false,
                ],
            ],
            $columns->map(
                static fn(Column $column): array => $column->toArray()
            )->toArray()
        );
    }

    public function testHasAutoIncrement(): void
    {
        $this->assertTrue(
            $this->schema
                ->table('test')
                ->hasAutoIncrement()
        );
    }

    public function testHasColumn(): void
    {
        $this->assertTrue(
            $this->schema
                ->table('test')
                ->hasColumn('name')
        );
    }

    public function testHasColumnInvalid(): void
    {
        $this->assertFalse(
            $this->schema
                ->table('test')
                ->hasColumn('invalid')
        );
    }
}
