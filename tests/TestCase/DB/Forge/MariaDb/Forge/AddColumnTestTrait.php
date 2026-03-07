<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Forge;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;

trait AddColumnTestTrait
{
    public function testAddColumnAfter(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
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

    public function testAddColumnCharsetCollation(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
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

    public function testAddColumnComment(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
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

    public function testAddColumnFirst(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'first' => true,
        ]);

        $this->assertSame(
            [
                'value',
                'id',
            ],
            $this->schema->table('test')
                ->columnNames()
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

    public function testAddColumnNullable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'mediumint',
                'length' => null,
                'precision' => 8,
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'bigint',
                'length' => null,
                'precision' => 20,
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

    public function testAddColumnTypeBlob(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => BinaryType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'blob',
                'length' => 65535,
                'precision' => null,
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'char',
                'length' => 1,
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

    public function testAddColumnTypeDateTime(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => DateTimeType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'datetime',
                'length' => null,
                'precision' => null,
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

    public function testAddColumnTypeEnum(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => EnumType::class,
            'values' => [
                'Y',
                'N',
            ],
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'enum',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => ['Y', 'N'],
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

    public function testAddColumnTypeFloat(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => FloatType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'float',
                'length' => null,
                'precision' => null,
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

    public function testAddColumnTypeInt(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
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

    public function testAddColumnTypeLongText(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => TextType::class,
            'length' => 4294967295,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'longtext',
                'length' => 4294967295,
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'mediumint',
                'length' => null,
                'precision' => 8,
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

    public function testAddColumnTypeMediumText(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => TextType::class,
            'length' => 16777215,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'mediumtext',
                'length' => 16777215,
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'smallint',
                'length' => null,
                'precision' => 6,
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

    public function testAddColumnTypeText(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => TextType::class,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'text',
                'length' => 65535,
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
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'tinyint',
                'length' => null,
                'precision' => 4,
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

    public function testAddColumnTypeTinyText(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => TextType::class,
            'length' => 255,
        ]);

        $this->assertSame(
            [
                'name' => 'value',
                'type' => 'tinytext',
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
