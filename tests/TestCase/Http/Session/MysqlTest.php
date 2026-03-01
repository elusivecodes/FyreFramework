<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\TypeParser;
use Fyre\Http\Session\Handlers\DatabaseSessionHandler;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\TestCase;

use function getenv;

final class MysqlTest extends TestCase
{
    protected Connection $db;

    protected DatabaseSessionHandler $handler;

    protected Session $session;

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

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(Config::class);
        $container->singleton(Session::class);
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
        ]);
        $container->use(Config::class)->set('Session', [
            'handler' => [
                'className' => DatabaseSessionHandler::class,
            ],
        ]);

        $this->db = $container->use(ConnectionManager::class)->use();

        $this->db->query('DROP TABLE IF EXISTS sessions');

        $this->db->query(<<<'EOT'
            CREATE TABLE sessions (
                id VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
                data BLOB NULL DEFAULT NULL,
                created DATETIME NOT NULL DEFAULT current_timestamp(),
                modified DATETIME NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->session = $container->use(Session::class);
        $this->handler = $this->session->getHandler();

        $this->session->start();

        $this->assertTrue(
            $this->handler->open('sessions', '')
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->destroy($id)
        );

        $this->assertTrue(
            $this->handler->close()
        );

        $this->db->query('DROP TABLE IF EXISTS sessions');
    }
}
