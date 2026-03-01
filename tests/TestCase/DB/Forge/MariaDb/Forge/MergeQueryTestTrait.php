<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Forge;

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
            ->execute();

        $this->assertSame(
            'int',
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
                ->hasColumn('test')
        );

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }
}
