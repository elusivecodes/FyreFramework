<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\IntegerType;

trait AddForeignKeyTestTrait
{
    public function testAddForeignKey(): void
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
        ]);

        $this->forge->addForeignKey('test', 'value_id', [
            'referencedTable' => 'test_values',
            'referencedColumns' => 'id',
            'onUpdate' => 'CASCADE',
            'onDelete' => 'CASCADE',
        ]);

        $this->assertTrue(
            $this->schema->table('test')
                ->hasForeignKey('value_id')
        );

        $this->assertSame(
            [
                'name' => 'value_id',
                'columns' => ['value_id'],
                'referencedTable' => 'test_values',
                'referencedColumns' => ['id'],
                'onUpdate' => 'CASCADE',
                'onDelete' => 'CASCADE',
            ],
            $this->schema->table('test')
                ->foreignKey('value_id')
                ->toArray()
        );
    }
}
