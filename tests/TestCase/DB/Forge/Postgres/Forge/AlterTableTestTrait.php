<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use Fyre\DB\Types\IntegerType;

trait AlterTableTestTrait
{
    public function testAlterTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->alterTable('test', [
            'comment' => 'This is the value',
        ]);

        $this->assertSame(
            [
                'name' => 'test',
                'comment' => 'This is the value',
            ],
            $this->schema->table('test')
                ->toArray()
        );
    }
}
