<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\MariaDb\Table;

use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Handlers\Mysql\MysqlColumn;
use Fyre\Utility\Collection;
use InvalidArgumentException;

trait ColumnTestTrait
{
    public function testColumn(): void
    {
        $column = $this->schema
            ->table('test')
            ->column('name');

        $this->assertInstanceOf(
            MysqlColumn::class,
            $column
        );

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

        $this->assertNull(
            $column->getValues()
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

        $this->assertSame(
            'utf8mb4',
            $column->getCharset()
        );

        $this->assertSame(
            'utf8mb4_unicode_ci',
            $column->getCollation()
        );

        $this->assertSame(
            '',
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
                'test',
                'bool',
                'date_precision',
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
                    'type' => 'int',
                    'length' => null,
                    'precision' => 10,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => null,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => true,
                ],
                'name' => [
                    'name' => 'name',
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'value' => [
                    'name' => 'value',
                    'type' => 'int',
                    'length' => null,
                    'precision' => 10,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 5,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'price' => [
                    'name' => 'price',
                    'type' => 'decimal',
                    'length' => null,
                    'precision' => 10,
                    'scale' => 2,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 2.5,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'text' => [
                    'name' => 'text',
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => 'default',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'test' => [
                    'name' => 'test',
                    'type' => 'enum',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => [
                        'Y',
                        'N',
                    ],
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => 'Y',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'bool' => [
                    'name' => 'bool',
                    'type' => 'tinyint',
                    'length' => null,
                    'precision' => 1,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => false,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'date_precision' => [
                    'name' => 'date_precision',
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => 6,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'created' => [
                    'name' => 'created',
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => new QueryLiteral('CURRENT_TIMESTAMP'),
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'modified' => [
                    'name' => 'modified',
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => new QueryLiteral('CURRENT_TIMESTAMP'),
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
            ],
            $columns->map(
                static fn(MysqlColumn $column): array => $column->toArray()
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
