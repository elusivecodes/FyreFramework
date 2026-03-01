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

trait AddColumnTestTrait
{
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

        $this->assertSame(
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
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'comment' => '',
                'autoIncrement' => false,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeBytea(): void
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
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeCharacter(): void
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
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeInteger(): void
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
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeNumeric(): void
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
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeReal(): void
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
                'length' => 6,
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
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'comment' => '',
                'autoIncrement' => false,
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
                'length' => null,
                'precision' => null,
                'scale' => null,
                'fractionalSeconds' => null,
                'nullable' => false,
                'unsigned' => false,
                'default' => null,
                'comment' => '',
                'autoIncrement' => false,
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }

    public function testAddColumnTypeTimestamp(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addColumn('test', 'value', [
            'type' => DateTimeFractionalType::class,
        ]);

        $this->assertSame(
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
            ],
            $this->schema->table('test')
                ->column('value')
                ->toArray()
        );
    }
}
