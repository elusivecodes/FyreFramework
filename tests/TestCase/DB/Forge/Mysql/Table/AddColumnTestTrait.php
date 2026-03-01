<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait AddColumnTestTrait
{
    public function testAddColumnExistingColumn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table column `test.id` already exists.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->addColumn('id', [
                'type' => IntegerType::class,
            ]);
    }

    public function testAddColumnSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD COLUMN value INT(11) NOT NULL AFTER id',
            ],
            $this->forge
                ->build('test')
                ->addColumn('value', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }

    public function testAddColumnSqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INT(11) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }
}
