<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use PHPUnit\Framework\Attributes\DataProvider;

trait AddColumnTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>}>
     */
    public static function addColumnTypeProvider(): array
    {
        return [
            'big int' => [
                [
                    'type' => IntegerType::class,
                    'precision' => 20,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
            'char' => [
                [
                    'type' => StringType::class,
                    'length' => 1,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
            'date time' => [
                ['type' => DateTimeType::class, 'nullable' => true, 'default' => null],
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
            ],
            'decimal' => [
                [
                    'type' => DecimalType::class,
                    'precision' => 10,
                    'scale' => 2,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
            'float' => [
                ['type' => FloatType::class, 'nullable' => true, 'default' => null],
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
            ],
            'int' => [
                ['type' => IntegerType::class, 'nullable' => true, 'default' => null],
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
            ],
            'medium int' => [
                [
                    'type' => IntegerType::class,
                    'precision' => 8,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
            'small int' => [
                [
                    'type' => IntegerType::class,
                    'precision' => 6,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
            'tiny int' => [
                [
                    'type' => IntegerType::class,
                    'precision' => 4,
                    'nullable' => true,
                    'default' => null,
                ],
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
            ],
        ];
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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
