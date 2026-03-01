<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Forge;

use Fyre\DB\Types\IntegerType;

trait RenameTableTestTrait
{
    public function testRenameTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->renameTable('test', 'other');

        $this->assertFalse(
            $this->schema->hasTable('test')
        );

        $this->assertTrue(
            $this->schema->hasTable('other')
        );
    }
}
