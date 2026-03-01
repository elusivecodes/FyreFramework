<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use Fyre\DB\Types\IntegerType;
use RuntimeException;

trait AddIndexTestTrait
{
    public function testAddIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'id_value', [
            'columns' => ['id', 'value'],
        ]);

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex('id_value')
        );

        $this->assertSame(
            [
                'name' => 'id_value',
                'columns' => ['id', 'value'],
                'unique' => false,
                'primary' => false,
                'type' => null,
            ],
            $this->schema->table('test')
                ->index('id_value')
                ->toArray()
        );
    }

    public function testAddIndexPrimary(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Primary keys cannot be added to existing tables.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'primary', [
            'columns' => ['id'],
            'primary' => true,
        ]);
    }

    public function testAddIndexUnique(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'value', [
            'unique' => true,
        ]);

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex('value')
        );

        $this->assertSame(
            [
                'name' => 'value',
                'columns' => ['value'],
                'unique' => true,
                'primary' => false,
                'type' => null,
            ],
            $this->schema->table('test')
                ->index('value')
                ->toArray()
        );
    }
}
