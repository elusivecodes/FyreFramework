<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Override;

use function mkdir;
use function rmdir;
use function unlink;

trait SqliteConnectionTrait
{
    protected Cacher $cache;

    protected Connection $db;

    protected Schema $schema;

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(CacheManager::class);
        $container->use(CacheManager::class)->setConfig('_schema', [
            'className' => FileCacher::class,
            'path' => 'tmp',
            'prefix' => 'schema.',
            'expire' => 3600,
        ]);

        $this->db = $container->use(ConnectionManager::class)->build([
            'className' => SqliteConnection::class,
        ]);

        $this->schema = $container->use(SchemaRegistry::class)->use($this->db);
        $this->cache = $container->use(CacheManager::class)->use('_schema');

        $this->db->query('DROP TABLE IF EXISTS test_values');
        $this->db->query('DROP TABLE IF EXISTS test');

        $this->db->query(<<<'EOT'
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
        EOT);
        $this->db->query(<<<'EOT'
            CREATE INDEX name_value ON test (name, value)
        EOT);
        $this->db->query(<<<'EOT'
            CREATE UNIQUE INDEX name ON test (name)
        EOT);
        $this->db->query(<<<'EOT'
            CREATE TABLE test_values (
                id UNSIGNED INTEGER NOT NULL,
                test_id UNSIGNED INTEGER NOT NULL DEFAULT '0',
                value UNSIGNED INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY (test_id) REFERENCES test (id) ON UPDATE CASCADE ON DELETE CASCADE
            )
        EOT);
        $this->db->query(<<<'EOT'
            CREATE INDEX test_values_test_id ON test_values (test_id)
        EOT);
        $this->db->query(<<<'EOT'
            CREATE INDEX value ON test_values (value)
        EOT);

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS test_values');
        $this->db->query('DROP TABLE IF EXISTS test');

        @unlink('tmp/schema._memory_.tables');
        @unlink('tmp/schema._memory_.test.columns');
        @unlink('tmp/schema._memory_.test.foreign_keys');
        @unlink('tmp/schema._memory_.test.indexes');
        @unlink('tmp/schema._memory_.test_values.columns');
        @unlink('tmp/schema._memory_.test_values.foreign_keys');
        @unlink('tmp/schema._memory_.test_values.indexes');
        @rmdir('tmp');
    }
}
