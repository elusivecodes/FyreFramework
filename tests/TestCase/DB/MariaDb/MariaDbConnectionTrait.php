<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\LogManager;
use Override;

use function getenv;
use function mkdir;
use function rmdir;
use function unlink;

trait MariaDbConnectionTrait
{
    protected ConnectionManager $connection;

    protected Connection $db;

    protected function insert(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
                [
                    'name' => 'Test 3',
                ],
            ])
            ->execute();
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
                'host' => getenv('MARIADB_HOST'),
                'username' => getenv('MARIADB_USERNAME'),
                'password' => getenv('MARIADB_PASSWORD'),
                'database' => getenv('MARIADB_DATABASE'),
                'port' => getenv('MARIADB_PORT'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
                'compress' => true,
                'persist' => true,
            ],
        ]);
        $container->use(Config::class)->set('Log', [
            'queries' => [
                'className' => FileLogger::class,
                'scopes' => ['queries'],
                'path' => 'log',
                'file' => 'queries',
            ],
        ]);

        @mkdir('log');

        $this->connection = $container->use(ConnectionManager::class);

        $this->db = $this->connection->use();

        $this->db->query('DROP TABLE IF EXISTS test');

        $this->db->query(<<<'EOT'
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
        $this->db->query('DROP TABLE IF EXISTS test');

        @unlink('log/queries-cli.log');
        @rmdir('log');
    }
}
