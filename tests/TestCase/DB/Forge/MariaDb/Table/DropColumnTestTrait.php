<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait DropColumnTestTrait
{
    public function testDropColumnInvalidColumn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table column `test.invalid` does not exist.');

        $this->forge
            ->build('test')
            ->dropColumn('invalid');
    }

    public function testDropColumnSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP COLUMN value',
            ],
            $this->forge
                ->build('test')
                ->dropColumn('value')
                ->sql()
        );
    }

    public function testDropColumnSqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INT(11) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => IntegerType::class,
                ])
                ->dropColumn('value')
                ->sql()
        );
    }
}
