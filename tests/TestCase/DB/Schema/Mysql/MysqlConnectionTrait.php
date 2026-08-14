<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Mysql;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Tests\TestCase\DB\Schema\Traits\ConnectionTrait;

use function getenv;

trait MysqlConnectionTrait
{
    use ConnectionTrait;

    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(Config::class);
        $container->singleton(CacheManager::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(SchemaRegistry::class);
        $container->use(CacheManager::class)->setConfig('_schema', [
            'className' => FileCacher::class,
            'path' => 'tmp',
            'prefix' => 'schema.',
            'expire' => 3600,
        ]);
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
                'persist' => true,
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
                value INT(10) UNSIGNED NOT NULL DEFAULT 5,
                price DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 2.50,
                text VARCHAR(255) NOT NULL DEFAULT 'default' COLLATE 'utf8mb4_unicode_ci',
                test ENUM('Y','N') NOT NULL DEFAULT 'Y',
                bool TINYINT(1) NOT NULL DEFAULT 0,
                date_precision DATETIME(6) NULL DEFAULT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                modified DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
                PRIMARY KEY (id),
                UNIQUE INDEX name (name),
                INDEX name_value (name, value)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);
        $db->query(<<<'SQL'
            CREATE TABLE test_values (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                test_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
                value INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                INDEX test_values_test_id (test_id),
                INDEX value (value),
                CONSTRAINT test_values_test_id FOREIGN KEY (test_id) REFERENCES test.test (id) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);
    }
}
