<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait ChangeColumnTestTrait
{
    public function testChangeColumnAfter(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
            ],
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'after' => 'id',
        ]);

        $this->assertSame(
            [
                'id',
                'value',
            ],
            $this->schema->table('test')
                ->columnNames()
        );
    }

    public function testChangeColumnAutoIncrement(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
        ]);

        $this->forge->changeColumn('test', 'id', [
            'type' => IntegerType::class,
            'autoIncrement' => true,
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
                'autoIncrement' => true,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('id')
                ->toArray()
        );
    }

    public function testChangeColumnCharsetCollation(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => StringType::class,
            'charset' => 'utf8mb3',
            'collation' => 'utf8mb3_unicode_ci',
        ]);

        $this->assertSame(
            [
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
                'charset' => 'utf8mb3',
                'collation' => 'utf8mb3_unicode_ci',
                'comment' => '',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnComment(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => StringType::class,
            'comment' => 'This is the value',
        ]);

        $this->assertSame(
            [
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
                'comment' => 'This is the value',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnDefault(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'default' => 1,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'int',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => 1,
                'charset' => null,
                'collation' => null,
                'comment' => '',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnFirst(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
            ],
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'id', [
            'first' => true,
        ]);

        $this->assertSame(
            [
                'id',
                'value',
            ],
            $this->schema->table('test')
                ->columnNames()
        );
    }

    public function testChangeColumnLength(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
            ],
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => StringType::class,
            'length' => 255,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'varchar',
                'length' => 255,
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
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnNullable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'nullable' => true,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'int',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'charset' => null,
                'collation' => null,
                'comment' => '',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnPrecision(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
            ],
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 9,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
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
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnRename(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'name' => 'other',
            'type' => IntegerType::class,
        ]);

        $this->assertFalse(
            $this->schema->table('test')
                ->hasColumn('value')
        );

        $this->assertTrue(
            $this->schema->table('test')
                ->hasColumn('other')
        );
    }

    public function testChangeColumnScale(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => DecimalType::class,
            'scale' => 2,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'decimal',
                'length' => null,
                'precision' => 10,
                'scale' => 2,
                'fractionalSeconds' => null,
                'values' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'charset' => null,
                'collation' => null,
                'comment' => '',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnType(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
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
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testChangeColumnUnsigned(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'unsigned' => true,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'int',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => null,
                'nullable' => false,
                'unsigned' => true,
                'default' => null,
                'charset' => null,
                'collation' => null,
                'comment' => '',
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }
}
