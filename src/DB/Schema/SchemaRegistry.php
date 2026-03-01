<?php
declare(strict_types=1);

namespace Fyre\DB\Schema;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\Handlers\Mysql\MysqlSchema;
use Fyre\DB\Schema\Handlers\Postgres\PostgresSchema;
use Fyre\DB\Schema\Handlers\Sqlite\SqliteSchema;
use InvalidArgumentException;
use WeakMap;

use function array_shift;
use function class_parents;
use function is_subclass_of;
use function ltrim;
use function sprintf;

/**
 * Resolves database-specific {@see Schema} implementations for a {@see Connection}.
 */
class SchemaRegistry
{
    use DebugTrait;

    /**
     * @var array<class-string<Connection>, class-string<Schema>>
     */
    protected array $handlers = [
        MysqlConnection::class => MysqlSchema::class,
        PostgresConnection::class => PostgresSchema::class,
        SqliteConnection::class => SqliteSchema::class,
    ];

    /**
     * @var WeakMap<Connection, Schema>
     */
    protected WeakMap $instances;

    /**
     * Constructs a SchemaRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {
        $this->instances = new WeakMap();
    }

    /**
     * Maps a Connection class to a Schema handler.
     *
     * @param class-string<Connection> $connectionClass The Connection class.
     * @param class-string<Schema> $schemaClass The Schema class.
     */
    public function map(string $connectionClass, string $schemaClass): void
    {
        /** @var class-string<Connection> $connectionClass */
        $connectionClass = ltrim($connectionClass, '\\');

        $this->handlers[$connectionClass] = $schemaClass;
    }

    /**
     * Loads a shared Schema for a Connection.
     *
     * @param Connection $connection The Connection.
     * @return Schema The Schema instance.
     */
    public function use(Connection $connection): Schema
    {
        return $this->instances[$connection] ??= $this->build($connection);
    }

    /**
     * Loads a Schema for a Connection.
     *
     * @param Connection $connection The Connection.
     * @return Schema The Schema instance.
     *
     * @throws InvalidArgumentException If the handler is missing or not valid.
     */
    protected function build(Connection $connection): Schema
    {
        $connectionKey = $connection::class;

        while (!isset($this->handlers[$connectionKey])) {
            $classParents ??= class_parents($connection::class);
            $connectionKey = array_shift($classParents);

            if (!$connectionKey) {
                throw new InvalidArgumentException(sprintf(
                    'Database connection `%s` does not have a mapped schema.',
                    $connection::class
                ));
            }
        }

        $schemaClass = (string) $this->handlers[$connectionKey];

        if (!is_subclass_of($schemaClass, Schema::class)) {
            throw new InvalidArgumentException(sprintf(
                'Database schema `%s` must extend `%s`.',
                $schemaClass,
                Schema::class
            ));
        }

        return $this->container->build($schemaClass, ['connection' => $connection]);
    }
}
