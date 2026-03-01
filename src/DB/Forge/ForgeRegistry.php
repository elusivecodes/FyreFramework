<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Forge\Handlers\Mysql\MysqlForge;
use Fyre\DB\Forge\Handlers\Postgres\PostgresForge;
use Fyre\DB\Forge\Handlers\Sqlite\SqliteForge;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use InvalidArgumentException;
use WeakMap;

use function array_shift;
use function class_parents;
use function is_subclass_of;
use function ltrim;
use function sprintf;

/**
 * Resolves database-specific {@see Forge} implementations for a {@see Connection}.
 */
class ForgeRegistry
{
    use DebugTrait;

    /**
     * @var array<class-string<Connection>, class-string<Forge>>
     */
    protected array $handlers = [
        MysqlConnection::class => MysqlForge::class,
        PostgresConnection::class => PostgresForge::class,
        SqliteConnection::class => SqliteForge::class,
    ];

    /**
     * @var WeakMap<Connection, Forge>
     */
    protected WeakMap $instances;

    /**
     * Constructs a ForgeRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {
        $this->instances = new WeakMap();
    }

    /**
     * Maps a Connection class to a Forge handler.
     *
     * @param class-string<Connection> $connectionClass The Connection class.
     * @param class-string<Forge> $forgeClass The Forge class.
     */
    public function map(string $connectionClass, string $forgeClass): void
    {
        /** @var class-string<Connection> $connectionClass */
        $connectionClass = ltrim($connectionClass, '\\');

        $this->handlers[$connectionClass] = $forgeClass;
    }

    /**
     * Loads a shared Forge for a Connection.
     *
     * @param Connection $connection The Connection.
     * @return Forge The Forge instance.
     */
    public function use(Connection $connection): Forge
    {
        return $this->instances[$connection] ??= $this->build($connection);
    }

    /**
     * Loads a Forge for a Connection.
     *
     * @param Connection $connection The Connection.
     * @return Forge The Forge instance.
     *
     * @throws InvalidArgumentException If the handler is missing or not valid.
     */
    protected function build(Connection $connection): Forge
    {
        $connectionKey = $connection::class;

        while (!isset($this->handlers[$connectionKey])) {
            $classParents ??= class_parents($connection::class);
            $connectionKey = array_shift($classParents);

            if (!$connectionKey) {
                throw new InvalidArgumentException(sprintf(
                    'Database connection `%s` does not have a mapped forge.',
                    $connection::class
                ));
            }
        }

        $forgeClass = (string) $this->handlers[$connectionKey];

        if (!is_subclass_of($forgeClass, Forge::class)) {
            throw new InvalidArgumentException(sprintf(
                'Database forge `%s` must extend `%s`.',
                $forgeClass,
                Forge::class
            ));
        }

        return $this->container->build($forgeClass, ['connection' => $connection]);
    }
}
