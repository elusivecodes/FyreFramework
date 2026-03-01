<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;

trait DropIndexTestTrait
{
    public function testDropIndexInvalidIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table index `test.invalid` does not exist.');

        $this->forge
            ->build('test')
            ->dropIndex('invalid');
    }

    public function testDropIndexSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'id' => [],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP INDEX id',
            ],
            $this->forge
                ->build('test')
                ->dropIndex('id')
                ->sql()
        );
    }

    public function testDropIndexSqlNewTable(): void
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
                ->addIndex('id')
                ->dropIndex('id')
                ->sql()
        );
    }
}
