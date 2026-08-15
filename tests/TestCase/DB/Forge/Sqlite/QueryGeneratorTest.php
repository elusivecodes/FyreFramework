<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite;

use Fyre\Core\Container;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Forge\Handlers\Sqlite\SqliteForge;
use Fyre\DB\Forge\Handlers\Sqlite\SqliteQueryGenerator;
use Fyre\DB\Forge\Handlers\Sqlite\SqliteTable;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestSchema;
use Tests\Mock\DB\TestSqliteConnection;

final class QueryGeneratorTest extends TestCase
{
    protected SqliteForge $forge;

    protected SqliteQueryGenerator $generator;

    protected SqliteTable $table;

    /**
     * @return array<string, mixed[]>
     */
    public static function columnProvider(): array
    {
        return [
            'fractional datetime' => [[
                'type' => 'datetime',
                'fractionalSeconds' => 3,
            ], '"value" DATETIME(3) NOT NULL'],
            'boolean default' => [[
                'type' => BooleanType::class,
                'default' => false,
            ], '"value" BOOLEAN NOT NULL DEFAULT 0'],
            'string default' => [[
                'type' => 'text',
                'default' => 'Test',
            ], '"value" TEXT NOT NULL DEFAULT \'Test\''],
            'numeric default' => [[
                'type' => 'numeric',
                'precision' => 10,
                'scale' => 2,
                'default' => '1.5',
            ], '"value" NUMERIC(10,2) NOT NULL DEFAULT 1.5'],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('columnProvider')]
    public function testBuildColumn(array $options, string $expected): void
    {
        $column = $this->table
            ->addColumn('value', $options)
            ->column('value');

        $this->assertSame(
            $expected,
            $this->generator->buildColumn($column)
        );
    }

    public function testBuildConstraintInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Constraint `value` is not valid.');

        $index = $this->table
            ->addIndex('value')
            ->index('value');

        $this->generator->buildConstraint($index);
    }

    public function testBuildCreateIndexPrimary(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Primary keys cannot be added to existing tables.');

        $index = $this->table
            ->setPrimaryKey('id')
            ->index('primary');

        $this->generator->buildCreateIndex($index);
    }

    public function testBuildCreateTableIfNotExists(): void
    {
        $this->table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS "test" ("id" INTEGER NOT NULL)',
            $this->generator->buildCreateTable($this->table, [
                'ifNotExists' => true,
            ])
        );
    }

    public function testBuildUnsupportedEnum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Column type `Fyre\\DB\\Types\\EnumType` is not supported by this connection.');

        $this->table->addColumn('value', [
            'type' => EnumType::class,
        ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(SchemaRegistry::class);

        $db = $container->build(TestSqliteConnection::class);

        $container->use(SchemaRegistry::class)
            ->map(TestSqliteConnection::class, TestSchema::class);

        $forge = $container->use(ForgeRegistry::class)->use($db);

        $this->assertInstanceOf(SqliteForge::class, $forge);

        $this->forge = $forge;
        $this->generator = $forge->generator();
        $this->table = $forge->build('test');
    }
}
