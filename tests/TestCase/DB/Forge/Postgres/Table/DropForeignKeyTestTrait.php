<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait DropForeignKeyTestTrait
{
    public function testDropForeignKeyInvalidForeignKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table foreign key `test.invalid` does not exist.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->dropForeignKey('invalid');
    }

    public function testDropForeignKeySqlExistingTable(): void
    {
        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'test_values_pkey' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
            ],
        ]);

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ], foreignKeys: [
            'value_id' => [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP CONSTRAINT value_id',
            ],
            $this->forge
                ->build('test')
                ->dropForeignKey('value_id')
                ->sql()
        );
    }

    public function testDropForeignKeySqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INTEGER NOT NULL, value_id INTEGER NOT NULL)',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value_id', [
                    'type' => IntegerType::class,
                ])
                ->addForeignKey('value_id', [
                    'referencedTable' => 'test_values',
                    'referencedColumns' => 'id',
                ])
                ->dropForeignKey('value_id')
                ->sql()
        );
    }
}
