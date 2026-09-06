<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use PHPUnit\Framework\Attributes\DataProvider;

trait AddIndexTestTrait
{
    /**
     * @return array<string, array{class-string<IntegerType>|class-string<StringType>, string, array<string, mixed>, array<string, mixed>}>
     */
    public static function addIndexProvider(): array
    {
        return [
            'default' => [
                IntegerType::class,
                'id_value',
                [
                    'columns' => ['id', 'value'],
                ],
                [
                    'name' => 'id_value',
                    'columns' => ['id', 'value'],
                    'unique' => false,
                    'primary' => false,
                    'type' => 'btree',
                ],
            ],
            'fulltext' => [
                StringType::class,
                'value',
                ['type' => 'FULLTEXT'],
                [
                    'name' => 'value',
                    'columns' => ['value'],
                    'unique' => false,
                    'primary' => false,
                    'type' => 'fulltext',
                ],
            ],
            'unique' => [
                IntegerType::class,
                'value',
                ['unique' => true],
                [
                    'name' => 'value',
                    'columns' => ['value'],
                    'unique' => true,
                    'primary' => false,
                    'type' => 'btree',
                ],
            ],
        ];
    }

    /**
     * @param class-string<IntegerType>|class-string<StringType> $columnType
     * @param array<string, mixed> $options
     * @param array<string, mixed> $expected
     */
    #[DataProvider('addIndexProvider')]
    public function testAddIndex(string $columnType, string $index, array $options, array $expected): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => $columnType,
            ],
        ]);

        $this->forge->addIndex('test', $index, $options);

        $this->assertTrue(
            $this->schema->table('test')
                ->hasIndex($index)
        );

        $this->assertArraysAreIdentical(
            $expected,
            $this->schema->table('test')
                ->index($index)
                ->toArray()
        );
    }

    public function testAddIndexPrimary(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge->addIndex('test', 'PRIMARY', [
            'columns' => ['id'],
        ]);

        $primaryKey = $this->schema->table('test')
            ->primaryKey();

        $this->assertIsArray($primaryKey);
        $this->assertArraysAreIdentical(
            [
                'id',
            ],
            $primaryKey
        );
    }
}
