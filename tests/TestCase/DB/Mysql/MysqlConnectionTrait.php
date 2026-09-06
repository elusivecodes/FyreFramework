<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\LogManager;
use Tests\TestCase\DB\Shared\ConnectionTrait;

use function getenv;

trait MysqlConnectionTrait
{
    use ConnectionTrait;

    protected Connection $db;

    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(LogManager::class);
        $container->use(Config::class)
            ->set('Database', [
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
                    'persist' => true,
                ],
            ])
            ->set('Log', [
                'queries' => [
                    'className' => FileLogger::class,
                    'scopes' => ['queries'],
                    'path' => 'log',
                    'file' => 'queries',
                ],
            ]);

        return $container;
    }

    protected static function createSchema(Connection $db): void
    {
        $db->query(<<<'SQL'
            CREATE TABLE test (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $db->query(<<<'SQL'
            CREATE TABLE fyre__locks (
                name VARCHAR(255) NOT NULL,
                owner VARCHAR(32) NOT NULL,
                expires DATETIME NOT NULL,
                PRIMARY KEY (name),
                INDEX fyre__locks__expires (expires)
            ) ENGINE=InnoDB
        SQL);
    }
}
