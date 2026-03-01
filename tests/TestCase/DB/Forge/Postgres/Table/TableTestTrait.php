<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\IntegerType;

trait TableTestTrait
{
    public function testOptionsExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'COMMENT ON TABLE test IS \'This is the value\'',
            ],
            $this->forge
                ->build('test', [
                    'comment' => 'This is the value',
                ])
                ->sql()
        );
    }

    public function testOptionsNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INTEGER NOT NULL)',
                'COMMENT ON TABLE test IS \'This is the value\'',
            ],
            $this->forge
                ->build('test', [
                    'comment' => 'This is the value',
                ])
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }
}
