<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait MergeQueryTestTrait
{
    public function testMergeQueries(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => StringType::class,
            ],
            'test' => [
                'type' => StringType::class,
            ],
        ], [
            'test_idx' => [
                'columns' => ['test'],
            ],
        ]);

        $this->assertSame(
            [
                'DROP INDEX test_idx',
                'ALTER TABLE test DROP COLUMN test, ALTER COLUMN id TYPE INTEGER USING CAST(id AS INTEGER), ADD COLUMN value INTEGER NOT NULL',
                'CREATE INDEX id ON test USING BTREE (id)',
            ],
            $this->forge
                ->build('test')
                ->changeColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => IntegerType::class,
                ])
                ->dropColumn('test')
                ->dropIndex('test_idx')
                ->addIndex('id')
                ->sql()
        );
    }
}
