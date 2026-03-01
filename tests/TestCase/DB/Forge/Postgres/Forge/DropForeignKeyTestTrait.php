<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use Fyre\DB\Types\IntegerType;

trait DropForeignKeyTestTrait
{
    public function testDropForeignKey(): void
    {
        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'test_values_pkey' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
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
        ]);

        $this->forge->dropForeignKey('test', 'value_id');

        $this->assertFalse(
            $this->schema->table('test')
                ->hasForeignKey('value_id')
        );
    }
}
