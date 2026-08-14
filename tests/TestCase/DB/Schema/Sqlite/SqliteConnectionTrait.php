<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Tests\TestCase\DB\Schema\Traits\ConnectionTrait;

trait SqliteConnectionTrait
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
                'className' => SqliteConnection::class,
                'database' => ':memory:',
                'mode' => 'memory',
                'cache' => 'shared',
            ],
        ]);

        return $container;
    }

    protected static function createSchema(Connection $db): void
    {
        $db->query(<<<'SQL'
            CREATE TABLE test (
                id UNSIGNED INTEGER NOT NULL,
                name VARCHAR(255) NULL DEFAULT NULL,
                value UNSIGNED INTEGER NOT NULL DEFAULT 5,
                price UNSIGNED NUMERIC(10,2) NOT NULL DEFAULT 2.50,
                text VARCHAR(255) NOT NULL DEFAULT 'default',
                bool BOOLEAN NOT NULL DEFAULT FALSE,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )
        SQL);
        $db->query(<<<'SQL'
            CREATE INDEX name_value ON test (name, value)
        SQL);
        $db->query(<<<'SQL'
            CREATE UNIQUE INDEX name ON test (name)
        SQL);
        $db->query(<<<'SQL'
            CREATE TABLE test_values (
                id UNSIGNED INTEGER NOT NULL,
                test_id UNSIGNED INTEGER NOT NULL DEFAULT '0',
                value UNSIGNED INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (test_id) REFERENCES test (id) ON UPDATE CASCADE ON DELETE CASCADE
            )
        SQL);
        $db->query(<<<'SQL'
            CREATE INDEX test_values_test_id ON test_values (test_id)
        SQL);
        $db->query(<<<'SQL'
            CREATE INDEX value ON test_values (value)
        SQL);
    }
}
