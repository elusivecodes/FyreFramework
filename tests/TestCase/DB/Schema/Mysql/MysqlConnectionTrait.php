<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Mysql;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Override;

use function getenv;
use function mkdir;
use function rmdir;
use function unlink;

trait MysqlConnectionTrait
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
        ]);

        $this->schema = $container->use(SchemaRegistry::class)->use($this->db);
        $this->cache = $container->use(CacheManager::class)->use('_schema');

        $this->db->query('DROP TABLE IF EXISTS test_values');
        $this->db->query('DROP TABLE IF EXISTS test');

        $this->db->query(<<<'EOT'
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
        EOT);
        $this->db->query(<<<'EOT'
            CREATE TABLE test_values (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                test_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
                value INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                INDEX test_values_test_id (test_id),
                INDEX value (value),
                CONSTRAINT test_values_test_id FOREIGN KEY (test_id) REFERENCES test.test (id) ON UPDATE CASCADE ON DELETE CASCADE
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS test_values');
        $this->db->query('DROP TABLE IF EXISTS test');

        @unlink('tmp/schema.test.tables');
        @unlink('tmp/schema.test.test.columns');
        @unlink('tmp/schema.test.test.foreign_keys');
        @unlink('tmp/schema.test.test.indexes');
        @unlink('tmp/schema.test.test_values.columns');
        @unlink('tmp/schema.test.test_values.foreign_keys');
        @unlink('tmp/schema.test.test_values.indexes');
        @rmdir('tmp');
    }
}
