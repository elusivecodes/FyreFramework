<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use Fyre\DB\Types\IntegerType;

trait DropColumnTestTrait
{
    public function testDropColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->dropColumn('test', 'value');

        $this->assertFalse(
            $this->schema->table('test')
                ->hasColumn('value')
        );
    }
}
