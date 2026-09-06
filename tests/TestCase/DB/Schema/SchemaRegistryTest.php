<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\Column;
use Fyre\DB\Schema\ForeignKey;
use Fyre\DB\Schema\Handlers\Sqlite\SqliteSchema;
use Fyre\DB\Schema\Index;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestSchema;
use Tests\Mock\DB\TestSqliteConnection;

use function class_uses;

final class SchemaRegistryTest extends TestCase
{
    /**
     * @var SqliteConnection&Stub
     */
    protected SqliteConnection $connection;

    protected Container $container;

    /**
     * @var Connection&Stub
     */
    protected Connection $missingConnection;

    protected SchemaRegistry $schemaRegistry;

    /**
     * @var Stub&TestSqliteConnection
     */
    protected TestSqliteConnection $subclassConnection;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(SchemaRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Schema::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Table::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Column::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Index::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(ForeignKey::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Schema::class)
        );
    }

    public function testMap(): void
    {
        $this->schemaRegistry->map(SqliteConnection::class, TestSchema::class);

        $schema = $this->schemaRegistry->use($this->connection);

        $this->assertInstanceOf(
            TestSchema::class,
            $schema
        );

        $this->assertSame(
            $this->connection,
            $schema->getConnection()
        );
    }

    public function testUse(): void
    {
        $schema = $this->schemaRegistry->use($this->connection);

        $this->assertInstanceOf(
            SqliteSchema::class,
            $schema
        );

        $this->assertSame(
            $this->connection,
            $schema->getConnection()
        );
    }

    public function testUseInvalidSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Database schema `Tests\TestCase\DB\Schema\SchemaRegistryTest` must extend `Fyre\DB\Schema\Schema`.');

        $this->schemaRegistry->map(SqliteConnection::class, self::class);
    }

    public function testUseMissingSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs(
            'Database connection `'.$this->missingConnection::class.'` does not have a mapped schema.'
        );

        $this->schemaRegistry->use($this->missingConnection);
    }

    public function testUseShared(): void
    {
        $this->assertSame(
            $this->schemaRegistry->use($this->connection),
            $this->schemaRegistry->use($this->connection)
        );
    }

    public function testUseSubclassConnection(): void
    {
        $schema = $this->schemaRegistry->use($this->subclassConnection);

        $this->assertInstanceOf(
            SqliteSchema::class,
            $schema
        );

        $this->assertSame(
            $this->subclassConnection,
            $schema->getConnection()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->schemaRegistry = $this->container->use(SchemaRegistry::class);

        $connection = $this->getStubBuilder(SqliteConnection::class)
            ->disableOriginalConstructor()
            ->getStub();
        $missingConnection = $this->createStub(Connection::class);
        $subclassConnection = $this->getStubBuilder(TestSqliteConnection::class)
            ->disableOriginalConstructor()
            ->getStub();

        $this->assertInstanceOf(SqliteConnection::class, $connection);
        $this->assertInstanceOf(TestSqliteConnection::class, $subclassConnection);

        $this->connection = $connection;
        $this->missingConnection = $missingConnection;
        $this->subclassConnection = $subclassConnection;
    }
}
