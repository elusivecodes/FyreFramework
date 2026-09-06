<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\DateTimeFractionalType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use PHPUnit\Framework\Attributes\DataProvider;

trait AddColumnTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>, array<string, mixed>}>
     */
    public static function addColumnTypeProvider(): array
    {
        return [
            'big int' => [
                ['type' => IntegerType::class],
                ['type' => IntegerType::class, 'precision' => 20],
                [
                    'name' => 'value',
                    'type' => 'bigint',
                    'length' => null,
                    'precision' => 20,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'bytea' => [
                ['type' => IntegerType::class],
                ['type' => BinaryType::class],
                [
                    'name' => 'value',
                    'type' => 'bytea',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'character' => [
                ['type' => IntegerType::class],
                ['type' => StringType::class, 'length' => 1],
                [
                    'name' => 'value',
                    'type' => 'character',
                    'length' => 1,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'integer' => [
                ['type' => IntegerType::class],
                ['type' => IntegerType::class],
                [
                    'name' => 'value',
                    'type' => 'integer',
                    'length' => null,
                    'precision' => 11,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'numeric' => [
                ['type' => IntegerType::class],
                ['type' => DecimalType::class, 'precision' => 10, 'scale' => 2],
                [
                    'name' => 'value',
                    'type' => 'numeric',
                    'length' => null,
                    'precision' => 10,
                    'scale' => 2,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'real' => [
                ['type' => IntegerType::class],
                ['type' => FloatType::class],
                [
                    'name' => 'value',
                    'type' => 'real',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'small int' => [
                ['type' => IntegerType::class, 'length' => 6],
                ['type' => IntegerType::class, 'precision' => 6],
                [
                    'name' => 'value',
                    'type' => 'smallint',
                    'length' => null,
                    'precision' => 6,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'text' => [
                ['type' => IntegerType::class],
                ['type' => TextType::class],
                [
                    'name' => 'value',
                    'type' => 'text',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
            'timestamp' => [
                ['type' => IntegerType::class],
                ['type' => DateTimeFractionalType::class],
                [
                    'name' => 'value',
                    'type' => 'timestamp without time zone',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => 6,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                    'enumClass' => null,
                ],
            ],
        ];
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
                'type' => 'character varying',
                'length' => 80,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
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
                'type' => 'integer',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => 1,
                'comment' => '',
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
        ]);

        $this->assertArraysAreIdentical(
            [
                'name' => 'value',
                'type' => 'character varying',
                'length' => 255,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
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
                'type' => 'integer',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => true,
                'unsigned' => false,
                'default' => null,
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
                'type' => 'numeric',
                'length' => null,
                'precision' => 10,
                'scale' => 2,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
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
     * @param array<string, mixed> $idOptions
     * @param array<string, mixed> $options
     * @param array<string, mixed> $expected
     */
    #[DataProvider('addColumnTypeProvider')]
    public function testAddColumnType(array $idOptions, array $options, array $expected): void
    {
        $this->forge->createTable('test', [
            'id' => $idOptions,
        ]);

        $this->forge->addColumn('test', 'value', $options);

        $this->assertArraysAreIdentical(
            $expected,
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }
}
