<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait AddColumnTestTrait
{
    public function testAddColumnDefault(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'default' => 1,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'integer',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => 1,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnLength(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => StringType::class,
            'length' => 255,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
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
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnPrecision(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 8,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'mediumint',
                'length' => null,
                'precision' => 8,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnScale(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => DecimalType::class,
            'scale' => 2,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'numeric',
                'length' => null,
                'precision' => 10,
                'scale' => 2,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeBigInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 20,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'bigint',
                'length' => null,
                'precision' => 20,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeChar(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => StringType::class,
            'length' => 1,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'char',
                'length' => 1,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeDateTime(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => DateTimeType::class,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'datetime',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeDecimal(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => DecimalType::class,
            'precision' => 10,
            'scale' => 2,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'numeric',
                'length' => null,
                'precision' => 10,
                'scale' => 2,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeFloat(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => FloatType::class,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'real',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'integer',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeMediumInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 8,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'mediumint',
                'length' => null,
                'precision' => 8,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeSmallInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 6,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'smallint',
                'length' => null,
                'precision' => 6,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeTinyInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'precision' => 4,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'tinyint',
                'length' => null,
                'precision' => 4,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnUnsigned(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => IntegerType::class,
            'unsigned' => true,
            'nullable' => true,
            'default' => null,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'integer',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => true,
                'default' => null,
                'comment' => null,
                'autoIncrement' => false,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }
}
