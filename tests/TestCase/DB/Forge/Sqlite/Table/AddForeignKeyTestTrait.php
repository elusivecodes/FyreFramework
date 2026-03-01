<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;
use RuntimeException;

trait AddForeignKeyTestTrait
{
    public function testAddForeignKeyExistingForeignKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table foreign key `test.test_value_id` already exists');

        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'primary' => [
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
            'test_value_id' => [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->forge
            ->build('test')
            ->addForeignKey('test_value_id', [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ]);
    }

    public function testAddForeignKeySqlExistingTable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Foreign keys cannot be added to existing tables.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->addForeignKey('test_value_id', [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ])
            ->sql();
    }

    public function testAddForeignKeySqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INTEGER NOT NULL, value_id INTEGER NOT NULL, CONSTRAINT test_value_id FOREIGN KEY (value_id) REFERENCES test_values (id))',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value_id', [
                    'type' => IntegerType::class,
                ])
                ->addForeignKey('test_value_id', [
                    'columns' => 'value_id',
                    'referencedTable' => 'test_values',
                    'referencedColumns' => 'id',
                ])
                ->sql()
        );
    }
}
