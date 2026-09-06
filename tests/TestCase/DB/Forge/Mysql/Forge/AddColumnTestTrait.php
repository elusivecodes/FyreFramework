<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\SetType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

trait AddColumnTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>}>
     */
    public static function addColumnTypeProvider(): array
    {
        return [
            'big int' => [
                ['type' => IntegerType::class, 'precision' => 20],
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
            ],
            'blob' => [
                ['type' => BinaryType::class],
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
            ],
            'char' => [
                ['type' => StringType::class, 'length' => 1],
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
            ],
            'date time' => [
                ['type' => DateTimeType::class],
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
            ],
            'decimal' => [
                ['type' => DecimalType::class, 'precision' => 10, 'scale' => 2],
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
            ],
            'float' => [
                ['type' => FloatType::class],
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
            ],
            'int' => [
                ['type' => IntegerType::class],
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
            ],
            'long text' => [
                ['type' => TextType::class, 'length' => 4294967295],
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
            ],
            'medium int' => [
                ['type' => IntegerType::class, 'precision' => 8],
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
            ],
            'medium text' => [
                ['type' => TextType::class, 'length' => 16777215],
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
            ],
            'small int' => [
                ['type' => IntegerType::class, 'precision' => 6],
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
            ],
            'text' => [
                ['type' => TextType::class],
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
            ],
            'tiny int' => [
                ['type' => IntegerType::class, 'precision' => 4],
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
            ],
            'tiny text' => [
                ['type' => TextType::class, 'length' => 255],
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
            ],
        ];
    }

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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $expected
     */
    #[DataProvider('addColumnTypeProvider')]
    public function testAddColumnType(array $options, array $expected): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', $options);

        $this->assertArraysAreIdentical(
            $expected,
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

        $this->assertArraysAreIdentical(
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

    public function testAddColumnTypeEnumWithBackedEnumClassValues(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => EnumType::class,
            'values' => Status::class,
        ]);

        $this->assertArraysAreIdentical(
            [
                'name' => 'value',
                'type' => 'enum',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => ['draft', 'published'],
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

    public function testAddColumnTypeEnumWithUnitEnumClassValues(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => EnumType::class,
            'values' => State::class,
        ]);

        $this->assertArraysAreIdentical(
            [
                'name' => 'value',
                'type' => 'enum',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => ['Draft', 'Published'],
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

    public function testAddColumnTypeSetWithEnumClassValues(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => SetType::class,
            'values' => State::class,
        ]);

        $this->assertArraysAreIdentical(
            [
                'name' => 'value',
                'type' => 'set',
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'values' => ['Draft', 'Published'],
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

        $this->assertArraysAreIdentical(
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
