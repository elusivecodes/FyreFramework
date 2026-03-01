<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait DropTestTrait
{
    public function testDropNewTable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table `test` does not exist.');

        $this->forge
            ->build('test')
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->drop();
    }

    public function testDropSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'DROP TABLE test',
            ],
            $this->forge
                ->build('test')
                ->drop()
                ->sql()
        );
    }
}
