<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\Forge\Column;
use Fyre\DB\Forge\ForeignKey;
use Fyre\DB\Forge\Forge;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Forge\Handlers\Sqlite\SqliteForge;
use Fyre\DB\Forge\Index;
use Fyre\DB\Forge\Table;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestForge;
use Tests\Mock\DB\TestSqliteConnection;

use function assert;
use function class_uses;

final class ForgeRegistryTest extends TestCase
{
    /**
     * @var SqliteConnection&Stub
     */
    protected SqliteConnection $connection;

    protected Container $container;

    protected ForgeRegistry $forgeRegistry;

    /**
     * @var Connection&Stub
     */
    protected Connection $missingConnection;

    /**
     * @var Stub&TestSqliteConnection
     */
    protected TestSqliteConnection $subclassConnection;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ForgeRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Forge::class)
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
            class_uses(Forge::class)
        );
    }

    public function testMap(): void
    {
        $this->forgeRegistry->map(SqliteConnection::class, TestForge::class);

        $forge = $this->forgeRegistry->use($this->connection);

        $this->assertInstanceOf(
            TestForge::class,
            $forge
        );

        $this->assertSame(
            $this->connection,
            $forge->getConnection()
        );
    }

    public function testUse(): void
    {
        $forge = $this->forgeRegistry->use($this->connection);

        $this->assertInstanceOf(
            SqliteForge::class,
            $forge
        );

        $this->assertSame(
            $this->connection,
            $forge->getConnection()
        );
    }

    public function testUseInvalidForge(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database forge `Tests\TestCase\DB\Forge\ForgeRegistryTest` must extend `Fyre\DB\Forge\Forge`.');

        $this->forgeRegistry->map(SqliteConnection::class, self::class);
    }

    public function testUseMissingForge(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not have a mapped forge.');

        $this->forgeRegistry->use($this->missingConnection);
    }

    public function testUseShared(): void
    {
        $this->assertSame(
            $this->forgeRegistry->use($this->connection),
            $this->forgeRegistry->use($this->connection)
        );
    }

    public function testUseSubclassConnection(): void
    {
        $forge = $this->forgeRegistry->use($this->subclassConnection);

        $this->assertInstanceOf(
            SqliteForge::class,
            $forge
        );

        $this->assertSame(
            $this->subclassConnection,
            $forge->getConnection()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->forgeRegistry = $this->container->use(ForgeRegistry::class);

        $connection = $this->getStubBuilder(SqliteConnection::class)
            ->disableOriginalConstructor()
            ->getStub();
        $missingConnection = $this->createStub(Connection::class);
        $subclassConnection = $this->getStubBuilder(TestSqliteConnection::class)
            ->disableOriginalConstructor()
            ->getStub();

        assert($connection instanceof SqliteConnection);
        assert($subclassConnection instanceof TestSqliteConnection);

        $this->connection = $connection;
        $this->missingConnection = $missingConnection;
        $this->subclassConnection = $subclassConnection;
    }
}
