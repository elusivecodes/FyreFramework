<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\IntegerType;

trait DropTableTestTrait
{
    public function testDropTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->dropTable('test');

        $this->assertFalse(
            $this->schema->hasTable('test')
        );
    }
}
