<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

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
                'ALTER TABLE test ENGINE = MyISAM',
            ],
            $this->forge
                ->build('test', [
                    'engine' => 'MyISAM',
                ])
                ->sql()
        );
    }

    public function testOptionsNewTable(): void
    {
        $this->assertSame(
            [
                'CREATE TABLE test (id INT(11) NOT NULL) ENGINE = MyISAM DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            ],
            $this->forge
                ->build('test', [
                    'engine' => 'MyISAM',
                ])
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }
}
