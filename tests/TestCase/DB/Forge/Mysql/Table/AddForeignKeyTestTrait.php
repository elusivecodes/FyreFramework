<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait AddForeignKeyTestTrait
{
    public function testAddForeignKeyExistingForeignKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table foreign key `test.value_id` already exists.');

        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
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

        $this->forge
            ->build('test')
            ->addForeignKey('value_id', [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ]);
    }

    public function testAddForeignKeySqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD CONSTRAINT value_id FOREIGN KEY (value_id) REFERENCES test_values (id)',
            ],
            $this->forge
                ->build('test')
                ->addForeignKey('value_id', [
                    'referencedTable' => 'test_values',
                    'referencedColumns' => 'id',
                ])
                ->sql()
        );
    }

    public function testAddForeignKeySqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INT(11) NOT NULL, value_id INT(11) NOT NULL, CONSTRAINT value_id FOREIGN KEY (value_id) REFERENCES test_values (id)) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
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
                ->sql()
        );
    }
}
