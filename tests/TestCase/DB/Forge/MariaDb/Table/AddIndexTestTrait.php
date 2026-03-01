<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait AddIndexTestTrait
{
    public function testAddIndexExistingIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table index `test.id` already exists.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'id' => [],
        ]);

        $this->forge
            ->build('test')
            ->addIndex('id');
    }

    public function testAddIndexSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD INDEX id (id) USING BTREE',
            ],
            $this->forge
                ->build('test')
                ->addIndex('id')
                ->sql()
        );
    }

    public function testAddIndexSqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INT(11) NOT NULL, INDEX id (id) USING BTREE) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addIndex('id')
                ->sql()
        );
    }
}
