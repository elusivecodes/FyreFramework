<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use InvalidArgumentException;

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
        $this->forge->createTable('test', [
            'id' => [
                'type' => StringType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ALTER COLUMN id TYPE INTEGER USING CAST(id AS INTEGER)',
            ],
            $this->forge
                ->build('test')
                ->changeColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
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
