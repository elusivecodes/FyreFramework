<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait MergeQueryTestTrait
{
    public function testMergeQueries(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'test' => [
                'type' => StringType::class,
            ],
        ], [
            'test_idx' => [
                'columns' => ['test'],
            ],
        ]);

        $this->forge
            ->build('test')
            ->addColumn('value', [
                'type' => IntegerType::class,
                'nullable' => true,
                'default' => 'NULL',
            ])
            ->dropColumn('test')
            ->dropIndex('test_idx')
            ->addIndex('id')
            ->execute();

        $this->assertSame(
            'integer',
            $this->schema->table('test')
                ->column('id')
                ->getType()
        );

        $this->assertTrue(
            $this->schema->table('test')
                ->hasColumn('value')
        );

        $this->assertFalse(
            $this->schema->table('test')
                ->hasIndex('test_idx')
        );

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }
}
