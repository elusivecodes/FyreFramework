<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

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

        $this->assertSame(
            [
                'ALTER TABLE test DROP FOREIGN KEY value_id',
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
                'CREATE TABLE test (id INT(11) NOT NULL, value_id INT(11) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
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
