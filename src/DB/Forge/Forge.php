<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;

/**
 * Provides a database schema builder (DDL).
 *
 * Provides a high-level API for creating/altering tables and generating DDL SQL.
 */
abstract class Forge
{
    use DebugTrait;
    use MacroTrait;

    protected QueryGenerator $generator;

    /**
     * Constructs a Forge.
     *
     * @param Container $container The Container.
     * @param Connection $connection The Connection.
     */
    public function __construct(
        protected Container $container,
        protected Connection $connection
    ) {}

    /**
     * Adds a column to a table.
     *
     * @param string $tableName The table name.
     * @param string $columnName The column name.
     * @param array<string, mixed> $options The column options.
     * @return static The Forge instance.
     */
    public function addColumn(string $tableName, string $columnName, array $options = []): static
    {
        $this->build($tableName)
            ->addColumn($columnName, $options)
            ->execute();

        return $this;
    }

    /**
     * Adds a foreign key to a table.
     *
     * @param string $tableName The table name.
     * @param string $foreignKeyName The foreign key name.
     * @param array<string, mixed> $options The foreign key options.
     * @return static The Forge instance.
     */
    public function addForeignKey(string $tableName, string $foreignKeyName, array $options = []): static
    {
        $this->build($tableName)
            ->addForeignKey($foreignKeyName, $options)
            ->execute();

        return $this;
    }

    /**
     * Adds an index to a table.
     *
     * @param string $tableName The table name.
     * @param string $indexName The index name.
     * @param array<string, mixed> $options The index options.
     * @return static The Forge instance.
     */
    public function addIndex(string $tableName, string $indexName, array $options = []): static
    {
        $this->build($tableName)
            ->addIndex($indexName, $options)
            ->execute();

        return $this;
    }

    /**
     * Alters a table.
     *
     * @param string $tableName The table name.
     * @param array<string, mixed> $options The table options.
     * @return static The Forge instance.
     */
    public function alterTable(string $tableName, array $options = []): static
    {
        $this->build($tableName, $options)
            ->execute();

        return $this;
    }

    /**
     * Builds a table schema.
     *
     * @param string $name The table name.
     * @param array<string, mixed> $options The table options.
     * @return Table The new Table instance.
     */
    abstract public function build(string $name, array $options = []): Table;

    /**
     * Changes a table column.
     *
     * @param string $tableName The table name.
     * @param string $columnName The column name.
     * @param array<string, mixed> $options The column options.
     * @return static The Forge instance.
     */
    public function changeColumn(string $tableName, string $columnName, array $options): static
    {
        $this->build($tableName)
            ->changeColumn($columnName, $options)
            ->execute();

        return $this;
    }

    /**
     * Creates a new table.
     *
     * @param string $tableName The table name.
     * @param array<string, array<string, mixed>> $columns The table columns.
     * @param array<string, array<string, mixed>> $indexes The table indexes.
     * @param array<string, array<string, mixed>> $foreignKeys The table foreign keys.
     * @param array<string, mixed> $options The table options.
     * @return static The Forge instance.
     */
    public function createTable(string $tableName, array $columns, array $indexes = [], array $foreignKeys = [], array $options = []): static
    {
        $table = $this->build($tableName, $options);

        foreach ($columns as $columnName => $options) {
            $table->addColumn($columnName, $options);
        }

        foreach ($indexes as $indexName => $options) {
            $table->addIndex($indexName, $options);
        }

        foreach ($foreignKeys as $foreignKeyName => $options) {
            $table->addForeignKey($foreignKeyName, $options);
        }

        $table->execute();

        return $this;
    }

    /**
     * Drops a column from a table.
     *
     * @param string $tableName The table name.
     * @param string $columnName The column name.
     * @return static The Forge instance.
     */
    public function dropColumn(string $tableName, string $columnName): static
    {
        $this->build($tableName)
            ->dropColumn($columnName)
            ->execute();

        return $this;
    }

    /**
     * Drops a foreign key from a table.
     *
     * @param string $tableName The table name.
     * @param string $foreignKeyName The foreign key name.
     * @return static The Forge instance.
     */
    public function dropForeignKey(string $tableName, string $foreignKeyName): static
    {
        $this->build($tableName)
            ->dropForeignKey($foreignKeyName)
            ->execute();

        return $this;
    }

    /**
     * Drops an index from a table.
     *
     * @param string $tableName The table name.
     * @param string $indexName The index name.
     * @return static The Forge instance.
     */
    public function dropIndex(string $tableName, string $indexName): static
    {
        $this->build($tableName)
            ->dropIndex($indexName)
            ->execute();

        return $this;
    }

    /**
     * Drops a table.
     *
     * @param string $tableName The table name.
     * @return static The Forge instance.
     */
    public function dropTable(string $tableName): static
    {
        $this->build($tableName)
            ->drop()
            ->execute();

        return $this;
    }

    /**
     * Returns the forge query generator.
     *
     * @return QueryGenerator The QueryGenerator instance.
     */
    abstract public function generator(): QueryGenerator;

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
     * Renames a column.
     *
     * @param string $tableName The table name.
     * @param string $columnName The old column name.
     * @param string $newColumnName The new column name.
     * @return static The Forge instance.
     */
    public function renameColumn(string $tableName, string $columnName, string $newColumnName): static
    {
        $this->build($tableName)
            ->changeColumn($columnName, ['name' => $newColumnName])
            ->execute();

        return $this;
    }

    /**
     * Renames a table.
     *
     * @param string $tableName The old table name.
     * @param string $newTableName The new table name.
     * @return static The Forge instance.
     */
    public function renameTable(string $tableName, string $newTableName): static
    {
        $this->build($tableName)
            ->rename($newTableName)
            ->execute();

        return $this;
    }
}
