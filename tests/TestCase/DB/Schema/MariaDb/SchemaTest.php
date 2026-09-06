<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\MariaDb;

use Fyre\DB\Schema\Handlers\Mysql\MysqlTable;
use Fyre\DB\Schema\Table;
use Fyre\Utility\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SchemaTest extends TestCase
{
    use MariaDbConnectionTrait;

    /**
     * @return array<string, array{string}>
     */
    public static function uca1400CharsetProvider(): array
    {
        return [
            'utf8mb3' => ['utf8mb3'],
            'utf8mb4' => ['utf8mb4'],
            'utf16' => ['utf16'],
            'utf32' => ['utf32'],
        ];
    }

    public function testGetConnection(): void
    {
        $this->assertSame(
            $this->db,
            $this->schema->getConnection()
        );
    }

    public function testGetDatabaseName(): void
    {
        $this->assertSame(
            'test',
            $this->schema->getDatabaseName()
        );
    }

    public function testHasTable(): void
    {
        $this->assertTrue(
            $this->schema->hasTable('test_values')
        );
    }

    public function testHasTableInvalid(): void
    {
        $this->assertFalse(
            $this->schema->hasTable('invalid')
        );
    }

    public function testTable(): void
    {
        $table = $this->schema->table('test');

        $this->assertInstanceOf(MysqlTable::class, $table);

        $this->assertSame(
            'test',
            $table->getName()
        );

        $this->assertSame(
            'InnoDB',
            $table->getEngine()
        );

        $this->assertSame(
            '',
            $table->getComment()
        );

        $this->assertSame(
            'utf8mb4',
            $table->getCharset()
        );

        $this->assertSame(
            'utf8mb4_unicode_ci',
            $table->getCollation()
        );
    }

    public function testTableInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Table `invalid` does not exist.');

        $this->schema->table('invalid');
    }

    public function testTableNames(): void
    {
        $this->assertArraysAreIdentical(
            [
                'test',
                'test_values',
            ],
            $this->schema->tableNames()
        );
    }

    public function testTables(): void
    {
        $tables = $this->schema->tables();

        $this->assertInstanceOf(Collection::class, $tables);

        $this->assertArraysAreIdentical(
            [
                'test' => [
                    'name' => 'test',
                    'engine' => 'InnoDB',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                ],
                'test_values' => [
                    'name' => 'test_values',
                    'engine' => 'InnoDB',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                ],
            ],
            $tables->map(
                static fn(Table $table): array => $table->toArray()
            )->toArray()
        );
    }

    #[DataProvider('uca1400CharsetProvider')]
    public function testTableUca1400Charset(string $charset): void
    {
        $this->db->query('CREATE TABLE test_collation (id INTEGER PRIMARY KEY) COLLATE '.$charset.'_uca1400_ai_ci');

        try {
            $table = $this->schema->table('test_collation');

            $this->assertInstanceOf(MysqlTable::class, $table);
            $this->assertSame(
                $charset,
                $table->getCharset()
            );
        } finally {
            $this->db->query('DROP TABLE test_collation');
        }
    }
}
