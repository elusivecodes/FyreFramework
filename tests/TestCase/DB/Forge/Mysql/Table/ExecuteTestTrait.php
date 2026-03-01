<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

use Fyre\DB\Types\IntegerType;

trait ExecuteTestTrait
{
    public function testExecuteAddColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->addColumn('value', [
                'type' => IntegerType::class,
            ])
            ->execute();

        $this->assertTrue(
            $this->schema->table('test')
                ->hasColumn('value')
        );
    }

    public function testExecuteAddForeignKey(): void
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

        $this->forge
            ->build('test')
            ->addForeignKey('value_id', [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ])
            ->execute();

        $this->assertTrue(
            $this->schema->table('test')
                ->hasForeignKey('value_id')
        );
    }

    public function testExecuteAddIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->addIndex('id')
            ->execute();

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }

    public function testExecuteChangeColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->changeColumn('value', [
                'name' => 'other',
            ])
            ->execute();

        $this->assertFalse(
            $this->schema->table('test')
                ->hasColumn('value')
        );

        $this->assertTrue(
            $this->schema->table('test')
                ->hasColumn('other')
        );
    }

    public function testExecuteCreateTable(): void
    {
        $this->forge
            ->build('test')
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->execute();

        $this->assertTrue(
            $this->schema->hasTable('test')
        );
    }

    public function testExecuteDrop(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->drop()
            ->execute();

        $this->assertFalse(
            $this->schema->hasTable('test')
        );
    }

    public function testExecuteDropColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->dropColumn('value')
            ->execute();

        $this->assertFalse(
            $this->schema->table('test')
                ->hasColumn('value')
        );
    }

    public function testExecuteDropForeignKey(): void
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
        ], foreignKeys: [
            'value_id' => [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->forge
            ->build('test')
            ->dropForeignKey('value_id')
            ->execute();

        $this->assertFalse(
            $this->schema->table('test')
                ->hasForeignKey('value_id')
        );
    }

    public function testExecuteDropIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'id' => [],
        ]);

        $this->forge
            ->build('test')
            ->dropIndex('id')
            ->execute();

        $this->assertFalse(
            $this->schema->table('test')
                ->hasIndex('id')
        );
    }

    public function testExecuteRename(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->rename('other')
            ->execute();

        $this->assertFalse(
            $this->schema->hasTable('test')
        );

        $this->assertTrue(
            $this->schema->hasTable('other')
        );
    }
}
