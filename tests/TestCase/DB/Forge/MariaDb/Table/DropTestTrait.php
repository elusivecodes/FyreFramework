<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait DropTestTrait
{
    public function testDropNewTable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Table `test` does not exist.');

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

        $this->assertArraysAreIdentical(
            [
                'DROP TABLE `test`',
            ],
            $this->forge
                ->build('test')
                ->drop()
                ->sql()
        );
    }
}
