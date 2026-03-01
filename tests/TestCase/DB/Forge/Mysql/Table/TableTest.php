<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

use Fyre\DB\Forge\ForeignKey;
use Fyre\DB\Forge\Handlers\Mysql\MysqlColumn;
use Fyre\DB\Forge\Handlers\Mysql\MysqlIndex;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Forge\Mysql\MysqlConnectionTrait;

use function array_map;

final class TableTest extends TestCase
{
    use AddColumnTestTrait;
    use AddForeignKeyTestTrait;
    use AddIndexTestTrait;
    use ChangeColumnTestTrait;
    use DiffDefaultsTestTrait;
    use DiffTestTrait;
    use DropColumnTestTrait;
    use DropForeignKeyTestTrait;
    use DropIndexTestTrait;
    use DropTestTrait;
    use ExecuteTestTrait;
    use MergeQueryTestTrait;
    use MysqlConnectionTrait;
    use RenameTestTrait;
    use TableTestTrait;

    public function testColumn(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value', [
            'type' => StringType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'id',
                'type' => 'int',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'charset' => null,
                'collation' => null,
                'comment' => '',
                'autoIncrement' => false,
            ],
            $table->column('id')
                ->toArray()
        );
    }

    public function testColumnNames(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value', [
            'type' => StringType::class,
        ]);

        $this->assertSame(
            [
                'id',
                'value',
            ],
            $table->columnNames()
        );
    }

    public function testColumns(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value', [
            'type' => StringType::class,
        ]);

        $this->assertSame(
            [
                'id' => [
                    'name' => 'id',
                    'type' => 'int',
                    'length' => null,
                    'precision' => 11,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'value' => [
                    'name' => 'value',
                    'type' => 'varchar',
                    'length' => 80,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
            ],
            array_map(
                static fn(MysqlColumn $column): array => $column->toArray(),
                $table->columns()
            )
        );
    }

    public function testForeignKey(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value_id', [
            'type' => IntegerType::class,
        ]);

        $table->addForeignKey('value_id', [
            'referencedTable' => 'test_values',
            'referencedColumns' => 'id',
        ]);

        $this->assertSame(
            [
                'name' => 'value_id',
                'columns' => [
                    'value_id',
                ],
                'referencedTable' => 'test_values',
                'referencedColumns' => [
                    'id',
                ],
                'onUpdate' => null,
                'onDelete' => null,
            ],
            $table->foreignKey('value_id')
                ->toArray()
        );
    }

    public function testForeignKeys(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value_id', [
            'type' => IntegerType::class,
        ]);

        $table->addForeignKey('value_id', [
            'referencedTable' => 'test_values',
            'referencedColumns' => 'id',
        ]);

        $this->assertSame(
            [
                'value_id' => [
                    'name' => 'value_id',
                    'columns' => [
                        'value_id',
                    ],
                    'referencedTable' => 'test_values',
                    'referencedColumns' => [
                        'id',
                    ],
                    'onUpdate' => null,
                    'onDelete' => null,
                ],
            ],
            array_map(
                static fn(ForeignKey $foreignKey): array => $foreignKey->toArray(),
                $table->foreignKeys()
            )
        );
    }

    public function testGetName(): void
    {
        $table = $this->forge->build('test');

        $this->assertSame(
            'test',
            $table->getName()
        );
    }

    public function testHasColumn(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $this->assertTrue(
            $table->hasColumn('id')
        );
    }

    public function testHasColumnFalse(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $this->assertFalse(
            $table->hasColumn('invalid')
        );
    }

    public function testHasForeignKey(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value_id', [
            'type' => IntegerType::class,
        ]);

        $table->addForeignKey('value_id', [
            'referencedTable' => 'test_values',
            'referencedColumns' => 'id',
        ]);

        $this->assertTrue(
            $table->hasForeignKey('value_id')
        );
    }

    public function testHasForeignKeyFalse(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addColumn('value_id', [
            'type' => IntegerType::class,
        ]);

        $this->assertFalse(
            $table->hasForeignKey('value_id')
        );
    }

    public function testHasIndex(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addIndex('id');

        $this->assertTrue(
            $table->hasIndex('id')
        );
    }

    public function testHasIndexFalse(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $this->assertFalse(
            $table->hasIndex('id')
        );
    }

    public function testIndex(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addIndex('id');

        $this->assertSame(
            [
                'name' => 'id',
                'columns' => [
                    'id',
                ],
                'unique' => false,
                'primary' => false,
                'type' => 'btree',
            ],
            $table->index('id')
                ->toArray()
        );
    }

    public function testIndexes(): void
    {
        $table = $this->forge->build('test');

        $table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $table->addIndex('id');

        $this->assertSame(
            [
                'id' => [
                    'name' => 'id',
                    'columns' => [
                        'id',
                    ],
                    'unique' => false,
                    'primary' => false,
                    'type' => 'btree',
                ],
            ],
            array_map(
                static fn(MysqlIndex $index): array => $index->toArray(),
                $table->indexes()
            )
        );
    }
}
