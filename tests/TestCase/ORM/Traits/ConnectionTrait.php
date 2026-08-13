<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Traits;

use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Override;

trait ConnectionTrait
{
    protected const TABLES = [
        'contains',
        'composite_items',
        'items',
        'others',
        'timestamps',
        'users',
        'addresses',
        'posts',
        'comments',
        'tags',
        'posts_tags',
    ];

    protected static Connection|null $schemaConnection = null;

    protected Container $container;

    protected Connection $db;

    protected ModelRegistry $modelRegistry;

    protected SchemaRegistry $schemaRegistry;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $container = static::buildContainer();

        static::$schemaConnection = $container->use(ConnectionManager::class)->use();

        static::dropSchema(static::$schemaConnection);
        static::createSchema(static::$schemaConnection);
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        if (static::$schemaConnection === null) {
            return;
        }

        try {
            static::dropSchema(static::$schemaConnection);
        } finally {
            static::$schemaConnection->disconnect();
            static::$schemaConnection = null;
        }
    }

    abstract protected static function buildContainer(): Container;

    abstract protected static function createSchema(Connection $db): void;

    #[Override]
    protected function setUp(): void
    {
        $this->container = static::buildContainer();
        $this->schemaRegistry = $this->container->use(SchemaRegistry::class);
        $this->modelRegistry = $this->container->use(ModelRegistry::class);

        $this->modelRegistry->addNamespace('Tests\Mock\Models\ORM');

        $this->container->use(EntityLocator::class)->addNamespace('Tests\Mock\Entities');

        $this->db = $this->container->use(ConnectionManager::class)->use();

        foreach (static::TABLES as $table) {
            $this->db->truncate($table);
        }
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->disconnect();
    }

    protected static function dropSchema(Connection $db): void
    {
        foreach (static::TABLES as $table) {
            $db->query('DROP TABLE IF EXISTS '.$table);
        }
    }
}
