<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\TypeParser;
use Fyre\Http\Session\Handlers\DatabaseSessionHandler;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\TestCase;

use function str_replace;

final class SqliteTest extends TestCase
{
    use DatabaseConnectionTrait;

    public function testGc(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data1')
        );

        $this->assertSame(
            1,
            $this->handler->gc(-1)
        );

        $this->assertSame(
            0,
            $this->db
                ->select()
                ->from('sessions')
                ->execute()
                ->count()
        );
    }

    public function testRead(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertSame(
            'data',
            $this->handler->read($id)
        );
    }

    public function testUpdate(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data1')
        );

        $this->assertSame(
            'data1',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data2')
        );

        $this->assertSame(
            'data2',
            $this->handler->read($id)
        );
    }

    public function testUpdateTimestamp(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertTrue(
            $this->handler->updateTimestamp($id, 'ignored')
        );

        $this->assertSame(
            'data',
            $this->handler->read($id)
        );

        $this->assertFalse(
            $this->handler->updateTimestamp('missing', 'ignored')
        );
    }

    public function testValidateId(): void
    {
        $id = $this->session->id();

        $this->assertFalse(
            $this->handler->validateId($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertTrue(
            $this->handler->validateId($id)
        );

        $this->db->update('sessions')
            ->set([
                'modified' => '2000-01-01 00:00:00',
            ])
            ->where([
                'id' => $id,
            ])
            ->execute();

        $this->assertFalse(
            $this->handler->validateId($id)
        );

        $this->assertFalse(
            $this->handler->validateId('../outside')
        );
    }

    #[Override]
    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(Config::class);
        $container->singleton(Session::class);
        $container->use(Config::class)->set('Database', [
            'default' => [
                'className' => SqliteConnection::class,
                'database' => 'session_'.str_replace('\\', '_', self::class),
                'mode' => 'memory',
                'cache' => 'shared',
            ],
        ]);
        $container->use(Config::class)->set('Session', [
            'handler' => [
                'className' => DatabaseSessionHandler::class,
            ],
        ]);

        return $container;
    }

    #[Override]
    protected static function createSchema(Connection $db): void
    {
        $db->query(<<<'SQL'
            CREATE TABLE sessions (
                id VARCHAR(40) NOT NULL,
                data BLOB NULL DEFAULT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )
        SQL);
    }
}
