<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Table;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use InvalidArgumentException;
use RuntimeException;

trait ChangeColumnTestTrait
{
    public function testChangeColumnInvalidColumn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table column `test.invalid` does not exist.');

        $this->forge
            ->build('test')
            ->changeColumn('invalid', [
                'type' => IntegerType::class,
            ]);
    }

    public function testChangeColumnSqlExistingTable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Columns cannot be modified in existing tables.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => StringType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->changeColumn('id', [
                'type' => IntegerType::class,
            ])
            ->sql();
    }

    public function testChangeColumnSqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INTEGER NOT NULL)',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => StringType::class,
                ])
                ->changeColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }
}
