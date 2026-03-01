<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

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
            'test' => [],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP INDEX test, DROP COLUMN test, CHANGE COLUMN id id INT(11) NOT NULL, ADD COLUMN value INT(11) NOT NULL AFTER id, ADD INDEX id (id) USING BTREE',
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
                ->dropIndex('test')
                ->addIndex('id')
                ->sql()
        );
    }
}
