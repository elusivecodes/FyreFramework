<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres;

use Fyre\Core\Container;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Forge\Handlers\Postgres\PostgresForge;
use Fyre\DB\Forge\Handlers\Postgres\PostgresQueryGenerator;
use Fyre\DB\Forge\Handlers\Postgres\PostgresTable;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestPostgresConnection;
use Tests\Mock\DB\TestSchema;

final class QueryGeneratorTest extends TestCase
{
    protected PostgresForge $forge;

    protected PostgresQueryGenerator $generator;

    protected PostgresTable $table;

    /**
     * @return array<string, mixed[]>
     */
    public static function columnProvider(): array
    {
        return [
            'fractional datetime with timezone' => [[
                'type' => DateTimeTimeZoneType::class,
                'fractionalSeconds' => 3,
            ], '"value" TIMESTAMP(3) WITH TIME ZONE NOT NULL'],
            'boolean default' => [[
                'type' => BooleanType::class,
                'default' => true,
            ], '"value" BOOLEAN NOT NULL DEFAULT TRUE'],
            'float default' => [[
                'type' => FloatType::class,
                'default' => '1.5',
            ], '"value" REAL NOT NULL DEFAULT 1.5'],
            'string default' => [[
                'type' => 'text',
                'default' => 'Test',
            ], '"value" TEXT NOT NULL DEFAULT \'Test\''],
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

    public function testBuildCommentOnColumnNull(): void
    {
        $column = $this->table
            ->addColumn('value', [
                'type' => IntegerType::class,
            ])
            ->column('value');

        $this->assertSame(
            'COMMENT ON COLUMN "test"."value" IS NULL',
            $this->generator->buildCommentOnColumn($column)
        );
    }

    public function testBuildCommentOnTableNull(): void
    {
        $this->assertSame(
            'COMMENT ON TABLE "test" IS NULL',
            $this->generator->buildCommentOnTable($this->table)
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

    public function testBuildConstraintInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Index type `hash` is not valid.');

        $index = $this->table
            ->addIndex('value', [
                'type' => 'hash',
                'unique' => true,
            ])
            ->index('value');

        $this->generator->buildConstraint($index);
    }

    public function testBuildCreateSchemaIfNotExists(): void
    {
        $this->assertSame(
            'CREATE SCHEMA IF NOT EXISTS "other"',
            $this->generator->buildCreateSchema('other', [
                'ifNotExists' => true,
            ])
        );
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

    public function testBuildDropSchemaIfExists(): void
    {
        $this->assertSame(
            'DROP SCHEMA IF EXISTS "other"',
            $this->generator->buildDropSchema('other', [
                'ifExists' => true,
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

        $db = $container->build(TestPostgresConnection::class);

        $container->use(SchemaRegistry::class)
            ->map(TestPostgresConnection::class, TestSchema::class);

        $forge = $container->use(ForgeRegistry::class)->use($db);

        $this->assertInstanceOf(PostgresForge::class, $forge);

        $this->forge = $forge;
        $this->generator = $forge->generator();
        $this->table = $forge->build('test');
    }
}
