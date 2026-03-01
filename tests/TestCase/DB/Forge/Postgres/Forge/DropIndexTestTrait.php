<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use Fyre\DB\Types\IntegerType;

trait DropIndexTestTrait
{
    public function testDropIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'id');

        $this->forge->dropIndex('test', 'id');

        $this->assertFalse(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }

    public function testDropPrimaryKey(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'test_pkey', [
            'columns' => ['id'],
            'primary' => true,
        ]);

        $this->forge->dropPrimaryKey('test');

        $this->assertFalse(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }

    public function testDropUniqueConstraint(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'id', [
            'unique' => true,
        ]);

        $this->forge->dropIndex('test', 'id');

        $this->assertFalse(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }
}
