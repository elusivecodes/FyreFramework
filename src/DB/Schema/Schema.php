<?php
declare(strict_types=1);

namespace Fyre\DB\Schema;

use Closure;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\Utility\Collection;
use Generator;
use InvalidArgumentException;

use function array_keys;
use function sprintf;
use function str_replace;

/**
 * Provides a database schema introspector.
 *
 * Provides access to tables and their columns/indexes/foreign keys, optionally cached.
 */
abstract class Schema
{
    use DebugTrait;
    use MacroTrait;

    protected const CACHE_KEY = '_schema';

    /**
     * @var array<string, Table>
     */
    protected array $loadedTables = [];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $tables = null;

    /**
     * Constructs a Schema.
     *
     * @param Container $container The Container.
     * @param CacheManager $cacheManager The CacheManager.
     * @param Connection $connection The Connection.
     */
    public function __construct(
        protected Container $container,
        protected CacheManager $cacheManager,
        protected Connection $connection
    ) {}

    /**
     * Clears the table data (including cache).
     */
    public function clear(): void
    {
        $this->tables = null;
        $this->loadedTables = [];

        $cache = $this->getCache();

        if ($cache) {
            $cache->delete($this->getCachePrefix().'.tables');
        }
    }

    /**
     * Returns the Cacher.
     *
     * @return Cacher|null The Cacher instance.
     */
    public function getCache(): Cacher|null
    {
        return $this->cacheManager->hasConfig(static::CACHE_KEY) ?
            $this->cacheManager->use(static::CACHE_KEY) :
            null;
    }

    /**
     * Returns the cache prefix.
     *
     * @return string The cache prefix.
     */
    public function getCachePrefix(): string
    {
        $config = $this->connection->getConfig();

        $prefix = $config['cacheKeyPrefix'] ?? '';
        $prefix = $prefix ? $prefix.'.' : '';

        return str_replace(':', '_', $prefix.$config['database']);
    }

    /**
     * Returns the Connection.
     *
     * @return Connection The Connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Returns the database name.
     *
     * @return string The database name.
     */
    public function getDatabaseName(): string
    {
        return $this->connection->getConfig()['database'] ?? '';
    }

    /**
     * Checks whether the schema has a table.
     *
     * @param string $name The table name.
     * @return bool Whether the schema has the table.
     */
    public function hasTable(string $name): bool
    {
        $this->loadTables();

        return isset($this->tables[$name]);
    }

    /**
     * Loads data via a callback using the cache.
     *
     * @param string $key The data key.
     * @param Closure(): array<string, mixed> $callback The data callback.
     * @return array<string, mixed> The data.
     */
    public function load(string $key, Closure $callback): array
    {
        $cache = $this->getCache();

        if (!$cache) {
            return $callback();
        }

        return $cache->remember(
            $this->getCachePrefix().'.'.$key,
            $callback
        );
    }

    /**
     * Loads a Table.
     *
     * @param string $name The table name.
     * @return Table The Table instance.
     *
     * @throws InvalidArgumentException If the table does not exist.
     */
    public function table(string $name): Table
    {
        $this->loadTables();

        if (!isset($this->tables[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table `%s` does not exist.',
                $name
            ));
        }

        return $this->loadedTables[$name] ??= $this->buildTable($name, $this->tables[$name]);
    }

    /**
     * Returns the names of all schema tables.
     *
     * @return string[] The names of all schema tables.
     */
    public function tableNames(): array
    {
        $this->loadTables();

        return array_keys($this->tables ?? []);
    }

    /**
     * Returns all schema tables.
     *
     * @return Collection<string, Table> The Collection instance containing the schema tables.
     */
    public function tables(): Collection
    {
        $this->loadTables();

        return new Collection(
            function(): Generator {
                foreach ($this->tables ?? [] as $name => $data) {
                    yield $name => $this->loadedTables[$name] ??= $this->buildTable($name, $data);
                }
            }
        );
    }

    /**
     * Builds a Table.
     *
     * @param string $name The table name.
     * @param array<string, mixed> $data The table data.
     * @return Table The new Table instance.
     */
    abstract protected function buildTable(string $name, array $data): Table;

    /**
     * Loads the schema tables data.
     */
    protected function loadTables(): void
    {
        $this->tables ??= $this->load(
            'tables',
            [$this, 'readTables'](...)
        );
    }

    /**
     * Reads the schema tables data.
     *
     * @return array<string, array<string, mixed>> The schema tables data keyed by table name.
     */
    abstract protected function readTables(): array;
}
