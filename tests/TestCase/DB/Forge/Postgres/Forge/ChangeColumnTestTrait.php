<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait ChangeColumnTestTrait
{
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
            'test_pk' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
            ],
        ]);

        $this->forge->changeColumn('test', 'id', [
            'type' => IntegerType::class,
            'autoIncrement' => true,
        ]);

        $this->assertSame(
            [
                'name' => 'id',
                'type' => 'integer',
                'length' => null,
                'precision' => 11,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'comment' => '',
                'autoIncrement' => true,
                'enumClass' => null,
            ],
            $this->schema->table('test')
                ->column('id')
                ->toArray()
        );
    }

    public function testChangeColumnAutoIncrementFalse(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
                'autoIncrement' => true,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ], [
            'test_pk' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
            ],
        ]);

        $this->forge->changeColumn('test', 'id', [
            'type' => IntegerType::class,
            'autoIncrement' => false,
        ]);

        $this->assertSame(
            [
                'name' => 'id',
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
            $this->schema->table('test')
                ->column('id')
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

    public function testChangeColumnDefault(): void
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
            'type' => IntegerType::class,
            'default' => 1,
        ]);

        $this->assertSame(
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

    public function testChangeColumnDefaultNull(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
                'default' => 1,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'default' => null,
        ]);

        $this->assertSame(
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
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'nullable' => true,
        ]);

        $this->assertSame(
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

    public function testChangeColumnNullableFalse(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
                'nullable' => true,
            ],
        ]);

        $this->forge->changeColumn('test', 'value', [
            'type' => IntegerType::class,
            'nullable' => false,
        ]);

        $this->assertSame(
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
            'cast' => true,
        ]);

        $this->assertSame(
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
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }
}
