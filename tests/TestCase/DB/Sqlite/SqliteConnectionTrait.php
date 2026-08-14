<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\LogManager;
use Tests\TestCase\DB\Traits\ConnectionTrait;

trait SqliteConnectionTrait
{
    use ConnectionTrait;

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
                    'className' => SqliteConnection::class,
                    'database' => ':memory:',
                    'mode' => 'memory',
                    'cache' => 'shared',
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
                id INTEGER NOT NULL,
                name VARCHAR(255) NULL DEFAULT NULL,
                PRIMARY KEY (id)
            )
        SQL);
    }
}
