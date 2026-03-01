<?php
declare(strict_types=1);

namespace Fyre\DB\Migration;

use DirectoryIterator;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Forge\Forge;
use Fyre\DB\Forge\ForgeRegistry;
use ReflectionClass;
use RegexIterator;

use function array_column;
use function in_array;
use function is_subclass_of;
use function ksort;
use function method_exists;
use function substr;

use const SORT_NATURAL;

/**
 * Runs migrations and updates migration history.
 *
 * Applies migrations in order and records execution to {@see MigrationHistory}.
 */
class MigrationRunner
{
    use DebugTrait;
    use NamespacesTrait;

    protected Connection|null $connection = null;

    protected MigrationHistory|null $history = null;

    /**
     * @var array<string, class-string<Migration>>|null
     */
    protected array|null $migrations = null;

    /**
     * Constructs a MigrationRunner.
     *
     * @param Container $container The Container.
     * @param Loader $loader The Loader.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param ForgeRegistry $forgeRegistry The ForgeRegistry.
     */
    public function __construct(
        protected Container $container,
        protected Loader $loader,
        protected ConnectionManager $connectionManager,
        protected ForgeRegistry $forgeRegistry
    ) {}

    /**
     * Clears loaded migrations.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->connection = null;
        $this->history = null;
        $this->migrations = null;
    }

    /**
     * Returns the Connection.
     *
     * @return Connection The Connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->connection ??= $this->connectionManager->use();
    }

    /**
     * Returns the Forge.
     *
     * @return Forge The Forge instance.
     */
    public function getForge(): Forge
    {
        return $this->forgeRegistry->use($this->getConnection());
    }

    /**
     * Returns the MigrationHistory.
     *
     * @return MigrationHistory The MigrationHistory instance.
     */
    public function getHistory(): MigrationHistory
    {
        return $this->history ??= $this->container->build(MigrationHistory::class, [
            'connection' => $this->getConnection(),
        ]);
    }

    /**
     * Returns all migrations.
     *
     * @return array<string, class-string<Migration>> The migrations.
     */
    public function getMigrations(): array
    {
        return $this->migrations ??= $this->findMigrations();
    }

    /**
     * Migrates to the latest version.
     *
     * Loads migrations via {@see MigrationRunner::getMigrations()} and skips any already
     * present in {@see MigrationHistory}. Migrations are instantiated with the current
     * {@see Forge} and `up()` is called when present.
     *
     * Note: Migrations are not automatically wrapped in a transaction.
     *
     * @return static The MigrationRunner instance.
     */
    public function migrate(): static
    {
        $migrations = $this->getMigrations();

        $history = $this->getHistory();

        $ranMigrations = $history->all();
        $ranMigrationNames = array_column($ranMigrations, 'migration');

        $batch = $history->getNextBatch();

        foreach ($migrations as $migrationName => $className) {
            if (in_array($migrationName, $ranMigrationNames, true)) {
                continue;
            }

            $migration = $this->container->build($className, ['forge' => $this->getForge()]);

            if (method_exists($migration, 'up')) {
                $this->container->call([$migration, 'up']);
            }

            $history->add($migrationName, $batch);
        }

        return $this;
    }

    /**
     * Rolls back to a previous version.
     *
     * Rollback order follows the migration history in reverse order (latest first). For each
     * matched migration class, `down()` is called when present, and the migration is removed
     * from history.
     *
     * @param int|null $batches The number of batches to rollback.
     * @param int|null $steps The number of steps to rollback.
     * @return static The MigrationRunner instance.
     */
    public function rollback(int|null $batches = 1, int|null $steps = null): static
    {
        $migrations = $this->getMigrations();

        $history = $this->getHistory();

        $ranMigrations = $history->all();

        $lastBatch = null;

        foreach ($ranMigrations as $data) {
            if ($batches !== null && $data['batch'] !== $lastBatch && $batches-- <= 0) {
                break;
            }

            if ($steps !== null && $steps-- <= 0) {
                break;
            }

            $migrationName = $data['migration'];
            $lastBatch = $data['batch'];

            if (isset($migrations[$migrationName])) {
                $migration = $this->container->build($migrations[$migrationName], ['forge' => $this->getForge()]);

                if (method_exists($migration, 'down')) {
                    $this->container->call([$migration, 'down']);
                }
            }

            $history->delete($migrationName);
        }

        return $this;
    }

    /**
     * Sets the Connection.
     *
     * @param Connection $connection The Connection.
     * @return static The MigrationRunner instance.
     */
    public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;
        $this->history = null;
        $this->migrations = null;

        return $this;
    }

    /**
     * Finds the migration classes.
     *
     * Migrations are discovered by scanning the configured namespaces for files matching
     * `Migration_*.php` and filtering to non-abstract subclasses of {@see Migration}.
     * The migration name is derived from the class short name after the `Migration_` prefix.
     *
     * @return array<string, class-string<Migration>> The migration classes.
     */
    protected function findMigrations(): array
    {
        $migrations = [];
        foreach ($this->namespaces as $namespace) {
            $folders = $this->loader->findFolders($namespace);

            foreach ($folders as $folder) {
                $directory = new DirectoryIterator($folder);
                $iterator = new RegexIterator($directory, '/^Migration_\w+.*\.php$/');

                foreach ($iterator as $item) {
                    if ($item->isDir()) {
                        continue;
                    }

                    $name = $item->getBasename('.php');

                    $className = $namespace.$name;

                    if (!is_subclass_of($className, Migration::class)) {
                        continue;
                    }

                    $reflection = new ReflectionClass($className);

                    if ($reflection->isAbstract()) {
                        continue;
                    }

                    $name = $reflection->getShortName();
                    $name = substr($name, 10);

                    $migrations[$name] = $className;
                }
            }
        }

        ksort($migrations, SORT_NATURAL);

        return $migrations;
    }
}
