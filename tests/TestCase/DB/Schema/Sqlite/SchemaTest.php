<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite;

use Fyre\DB\Schema\Handlers\Sqlite\SqliteTable;
use Fyre\Utility\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SchemaTest extends TestCase
{
    use SqliteConnectionTrait;

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
            ':memory:',
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

        $this->assertInstanceOf(SqliteTable::class, $table);

        $this->assertSame(
            'test',
            $table->getName()
        );

        $this->assertNull(
            $table->getComment()
        );
    }

    public function testTableInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table `invalid` does not exist.');

        $this->schema->table('invalid');
    }

    public function testTableNames(): void
    {
        $this->assertSame(
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

        $this->assertSame(
            [
                'test' => [
                    'name' => 'test',
                    'comment' => null,
                ],
                'test_values' => [
                    'name' => 'test_values',
                    'comment' => null,
                ],
            ],
            $tables->map(
                static fn(SqliteTable $table): array => $table->toArray()
            )->toArray()
        );
    }
}
