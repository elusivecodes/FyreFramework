<?php
declare(strict_types=1);

namespace Tests\TestCase\DB;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\ConnectionRetry;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Queries\DeleteQuery;
use Fyre\DB\Queries\InsertQuery;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Queries\UpdateQuery;
use Fyre\DB\Queries\UpsertQuery;
use Fyre\DB\Query;
use Fyre\DB\QueryGenerator;
use Fyre\DB\QueryLiteral;
use Fyre\DB\ResultSet;
use Fyre\DB\TypeParser;
use Fyre\DB\ValueBinder;
use Fyre\Event\EventManager;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function getenv;

final class ConnectionManagerTest extends TestCase
{
    protected ConnectionManager $connectionManager;

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database connection `Invalid` must extend `Fyre\DB\Connection`.');

        $this->connectionManager->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ConnectionManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(ConnectionRetry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Query::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(QueryGenerator::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(QueryLiteral::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(ResultSet::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(ValueBinder::class)
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => MysqlConnection::class,
                    'host' => getenv('MYSQL_HOST'),
                    'username' => getenv('MYSQL_USERNAME'),
                    'password' => getenv('MYSQL_PASSWORD'),
                    'database' => getenv('MYSQL_DATABASE'),
                    'port' => getenv('MYSQL_PORT'),
                    'collation' => 'utf8mb4_unicode_ci',
                    'charset' => 'utf8mb4',
                    'compress' => true,
                ],
                'other' => [
                    'className' => MysqlConnection::class,
                    'host' => getenv('MYSQL_HOST'),
                    'username' => getenv('MYSQL_USERNAME'),
                    'password' => getenv('MYSQL_PASSWORD'),
                    'database' => getenv('MYSQL_DATABASE'),
                    'port' => getenv('MYSQL_PORT'),
                    'collation' => 'utf8mb4_unicode_ci',
                    'charset' => 'utf8mb4',
                    'compress' => true,
                ],
            ],
            $this->connectionManager->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => MysqlConnection::class,
                'host' => getenv('MYSQL_HOST'),
                'username' => getenv('MYSQL_USERNAME'),
                'password' => getenv('MYSQL_PASSWORD'),
                'database' => getenv('MYSQL_DATABASE'),
                'port' => getenv('MYSQL_PORT'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
                'compress' => true,
            ],
            $this->connectionManager->getConfig('default')
        );
    }

    public function testIsLoaded(): void
    {
        $this->connectionManager->use();

        $this->assertTrue(
            $this->connectionManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->connectionManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->connectionManager->use('other');

        $this->assertTrue(
            $this->connectionManager->isLoaded('other')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Connection::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(ResultSet::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(DeleteQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(InsertQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(SelectQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(UpdateQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(UpsertQuery::class)
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->connectionManager,
            $this->connectionManager->setConfig('test', [
                'className' => MysqlConnection::class,
            ])
        );

        $this->assertSame(
            [
                'className' => MysqlConnection::class,
            ],
            $this->connectionManager->getConfig('test')
        );

        $this->connectionManager->unload('test');
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database connection config `default` already exists.');

        $this->connectionManager->setConfig('default', [
            'className' => MysqlConnection::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->connectionManager->use();

        $this->assertSame(
            $this->connectionManager,
            $this->connectionManager->unload()
        );

        $this->assertFalse(
            $this->connectionManager->isLoaded()
        );
        $this->assertFalse(
            $this->connectionManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->connectionManager,
            $this->connectionManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->connectionManager->use('other');

        $this->assertSame(
            $this->connectionManager,
            $this->connectionManager->unload('other')
        );

        $this->assertFalse(
            $this->connectionManager->isLoaded('other')
        );
        $this->assertFalse(
            $this->connectionManager->hasConfig('other')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->connectionManager->use();
        $handler2 = $this->connectionManager->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            MysqlConnection::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(LogManager::class);
        $container->use(Config::class)->set('Database', [
            'default' => [
                'className' => MysqlConnection::class,
                'host' => getenv('MYSQL_HOST'),
                'username' => getenv('MYSQL_USERNAME'),
                'password' => getenv('MYSQL_PASSWORD'),
                'database' => getenv('MYSQL_DATABASE'),
                'port' => getenv('MYSQL_PORT'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
                'compress' => true,
            ],
            'other' => [
                'className' => MysqlConnection::class,
                'host' => getenv('MYSQL_HOST'),
                'username' => getenv('MYSQL_USERNAME'),
                'password' => getenv('MYSQL_PASSWORD'),
                'database' => getenv('MYSQL_DATABASE'),
                'port' => getenv('MYSQL_PORT'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
                'compress' => true,
            ],
        ]);

        $this->connectionManager = $container->use(ConnectionManager::class);

        $db = $this->connectionManager->use();

        $db->query('DROP TABLE IF EXISTS test');

        $db->query(<<<'EOT'
            CREATE TABLE test (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);
    }

    #[Override]
    protected function tearDown(): void
    {
        if (!$this->connectionManager->hasConfig()) {
            return;
        }

        $db = $this->connectionManager->use();
        $db->query('DROP TABLE IF EXISTS test');
    }
}
