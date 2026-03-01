<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait CreateTableTestTrait
{
    public function testCreateTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
                'autoIncrement' => true,
            ],
            'value' => [
                'type' => StringType::class,
                'length' => 255,
            ],
            'created' => [
                'type' => DateTimeType::class,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'modified' => [
                'type' => DateTimeType::class,
                'nullable' => true,
                'default' => null,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
        ], options: [
            'engine' => 'MyISAM',
            'charset' => 'utf8mb3',
            'collation' => 'utf8mb3_unicode_ci',
        ]);

        $this->assertTrue(
            $this->schema->hasTable('test')
        );

        $this->assertSame(
            [
                'name' => 'test',
                'engine' => 'MyISAM',
                'charset' => 'utf8mb3',
                'collation' => 'utf8mb3_unicode_ci',
                'comment' => '',
            ],
            $this->schema->table('test')->toArray()
        );
    }
}
