<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

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
        ]);
        $this->forge->addIndex('test', 'id');

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
                'CREATE INDEX id ON test USING BTREE (id)',
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
                'CREATE TABLE test (id INTEGER NOT NULL)',
                'CREATE INDEX id ON test USING BTREE (id)',
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
