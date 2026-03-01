<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\IntegerType;

trait RenameTestTrait
{
    public function testRenameSqlExistingTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test RENAME TO other',
            ],
            $this->forge
                ->build('test')
                ->rename('other')
                ->sql()
        );
    }

    public function testRenameSqlNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE other (id INT(11) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            ],
            $this->forge
                ->build('test')
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->rename('other')
                ->sql()
        );
    }
}
