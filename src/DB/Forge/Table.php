<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Schema\Column as SchemaColumn;
use Fyre\DB\Schema\ForeignKey as SchemaForeignKey;
use Fyre\DB\Schema\Index as SchemaIndex;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table as SchemaTable;
use InvalidArgumentException;

use function array_diff_key;
use function array_keys;
use function array_merge;
use function array_replace;
use function array_search;
use function array_slice;
use function sprintf;

/**
 * Builds table schemas for DDL operations.
 *
 * Represents a table definition and queued alter/create/drop operations.
 */
abstract class Table
{
    use DebugTrait;

    /**
     * @var array<string, Column>
     */
    protected array $columns = [];

    protected bool $dropTable = false;

    /**
     * @var array<string, ForeignKey>
     */
    protected array $foreignKeys = [];

    /**
     * @var array<string, Index>
     */
    protected array $indexes = [];

    protected string|null $newName = null;

    /**
     * @var array<string, string>
     */
    protected array $renameColumns = [];

    protected Schema $schema;

    protected SchemaTable|null $schemaTable = null;

    /**
     * Constructs a Table.
     *
     * @param Container $container The Container.
     * @param Forge $forge The Forge.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     * @param string $name The table name.
     * @param string|null $comment The table comment.
     */
    public function __construct(
        protected Container $container,
        protected Forge $forge,
        SchemaRegistry $schemaRegistry,
        protected string $name,
        protected string|null $comment = null,
    ) {
        $connection = $this->forge->getConnection();
        $this->schema = $schemaRegistry->use($connection);

        if (!$this->schema->hasTable($this->name)) {
            return;
        }

        $this->reloadSchema();
    }

    /**
     * Adds a column to the table.
     *
     * @param string $name The column name.
     * @param array<string, mixed> $options The column options (e.g. `type`, `length`, `precision`, `nullable`,
     *                                      `unsigned`, `default`, `comment`, `autoIncrement`; handler implementations may support additional keys).
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the column already exists.
     */
    public function addColumn(string $name, array $options = []): static
    {
        if ($this->hasColumn($name)) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` already exists.',
                $this->name,
                $name
            ));
        }

        $this->columns[$name] = $this->buildColumn($name, $options);

        return $this;
    }

    /**
     * Adds a foreign key to the table.
     *
     * @param string $name The foreign key name.
     * @param array<string, mixed> $options The foreign key options.
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the foreign key already exists.
     */
    public function addForeignKey(string $name, array $options = []): static
    {
        if ($this->hasForeignKey($name)) {
            throw new InvalidArgumentException(sprintf(
                'Table foreign key `%s.%s` already exists.',
                $this->name,
                $name
            ));
        }

        $options['columns'] ??= $name;

        $this->foreignKeys[$name] = $this->buildForeignKey($name, $options);

        return $this;
    }

    /**
     * Adds an index to the table.
     *
     * @param string $name The index name.
     * @param array<string, mixed> $options The index options.
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the index already exists.
     */
    public function addIndex(string $name, array $options = []): static
    {
        if ($this->hasIndex($name)) {
            throw new InvalidArgumentException(sprintf(
                'Table index `%s.%s` already exists.',
                $this->name,
                $name
            ));
        }

        $options['columns'] ??= $name;

        $this->indexes[$name] = $this->buildIndex($name, $options);

        return $this;
    }

    /**
     * Changes a table column.
     *
     * @param string $name The column name.
     * @param array<string, mixed> $options The column options (supports `name` for renames; see {@see self::addColumn()}
     *                                      for common keys; handler implementations may support additional keys).
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the column does not exist.
     */
    public function changeColumn(string $name, array $options): static
    {
        if (!$this->hasColumn($name)) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        $newName = $options['name'] ?? $name;
        $oldOptions = $this->columns[$name]->toArray();

        unset($options['name']);
        unset($oldOptions['name']);

        if (isset($options['type']) && $options['type'] !== $oldOptions['type']) {
            $options['length'] ??= null;
        }

        $options = array_replace($oldOptions, $options);

        $this->columns[$newName] = $this->buildColumn($newName, $options);

        if ($newName !== $name) {
            $this->renameColumns[$name] = $newName;
            static::updateColumnOrder($newName, after: $name);
            unset($this->columns[$name]);
        }

        return $this;
    }

    /**
     * Clears the column and index data.
     *
     * @return static The Table instance.
     */
    public function clear(): static
    {
        $this->columns = [];
        $this->indexes = [];
        $this->foreignKeys = [];
        $this->renameColumns = [];

        return $this;
    }

    /**
     * Returns a table column.
     *
     * @param string $name The column name.
     * @return Column The Column instance.
     *
     * @throws InvalidArgumentException If the column does not exist.
     */
    public function column(string $name): Column
    {
        if (!isset($this->columns[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->columns[$name];
    }

    /**
     * Returns the names of all table columns.
     *
     * @return string[] The names of all table columns.
     */
    public function columnNames(): array
    {
        return array_keys($this->columns);
    }

    /**
     * Returns all table columns.
     *
     * @return array<string, Column> The table columns keyed by column name.
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * Checks whether this table is equivalent to a SchemaTable.
     *
     * @param SchemaTable $schemaTable The SchemaTable.
     * @return bool Whether the tables are equivalent.
     */
    public function compare(SchemaTable $schemaTable): bool
    {
        return $this->comment === $schemaTable->getComment();
    }

    /**
     * Drops the table.
     *
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the table does not exist.
     */
    public function drop(): static
    {
        if (!$this->schemaTable) {
            throw new InvalidArgumentException(sprintf(
                'Table `%s` does not exist.',
                $this->name
            ));
        }

        $this->dropTable = true;

        return $this;
    }

    /**
     * Drops a column from the table.
     *
     * @param string $column The column name.
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the column does not exist.
     */
    public function dropColumn(string $column): static
    {
        if (!$this->hasColumn($column)) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` does not exist.',
                $this->name,
                $column
            ));
        }

        unset($this->columns[$column]);

        return $this;
    }

    /**
     * Drops a foreign key from the table.
     *
     * @param string $foreignKey The foreign key name.
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the foreign key does not exist.
     */
    public function dropForeignKey(string $foreignKey): static
    {
        if (!$this->hasForeignKey($foreignKey)) {
            throw new InvalidArgumentException(sprintf(
                'Table foreign key `%s.%s` does not exist.',
                $this->name,
                $foreignKey
            ));
        }

        unset($this->foreignKeys[$foreignKey]);
        unset($this->indexes[$foreignKey]);

        return $this;
    }

    /**
     * Drops an index from the table.
     *
     * @param string $index The index name.
     * @return static The Table instance.
     *
     * @throws InvalidArgumentException If the index does not exist.
     */
    public function dropIndex(string $index): static
    {
        if (!$this->hasIndex($index)) {
            throw new InvalidArgumentException(sprintf(
                'Table index `%s.%s` does not exist.',
                $this->name,
                $index
            ));
        }

        unset($this->foreignKeys[$index]);
        unset($this->indexes[$index]);

        return $this;
    }

    /**
     * Generates and executes the SQL queries.
     *
     * Note: This executes the queries immediately, clears any queued operations, and refreshes cached schema
     * state as needed (including table renames and drops).
     *
     * @return static The Table instance.
     */
    public function execute(): static
    {
        $queries = $this->sql();

        $connection = $this->forge->getConnection();

        foreach ($queries as $sql) {
            $connection->query($sql);
        }

        $this->clear();

        if ($this->schemaTable) {
            $this->schemaTable->clear();
        }

        if (
            !$this->schemaTable ||
            $this->newName ||
            $this->dropTable ||
            !$this->compare($this->schemaTable)
        ) {
            $this->schema->clear();
        }

        if ($this->newName) {
            $this->name = $this->newName;
            $this->newName = null;
        }

        if ($this->dropTable) {
            $this->schemaTable = null;
            $this->dropTable = false;
        } else {
            $this->reloadSchema(true);
        }

        return $this;
    }

    /**
     * Returns a table foreign key.
     *
     * @param string $name The foreign key name.
     * @return ForeignKey The ForeignKey instance.
     *
     * @throws InvalidArgumentException If the foreign key does not exist.
     */
    public function foreignKey(string $name): ForeignKey
    {
        if (!isset($this->foreignKeys[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table foreign key `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->foreignKeys[$name];
    }

    /**
     * Returns all table foreign keys.
     *
     * @return array<string, ForeignKey> The table foreign keys keyed by foreign key name.
     */
    public function foreignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     * Returns the table comment.
     *
     * @return string|null The table comment.
     */
    public function getComment(): string|null
    {
        return $this->comment;
    }

    /**
     * Returns the Forge.
     *
     * @return Forge The Forge instance.
     */
    public function getForge(): Forge
    {
        return $this->forge;
    }

    /**
     * Returns the table name.
     *
     * @return string The table name.
     */
    public function getName(): string
    {
        return $this->newName ?? $this->name;
    }

    /**
     * Checks whether the table has a column.
     *
     * @param string $name The column name.
     * @return bool Whether the table has the column.
     */
    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    /**
     * Checks whether the table has a foreign key.
     *
     * @param string $name The foreign key name.
     * @return bool Whether the table has the foreign key.
     */
    public function hasForeignKey(string $name): bool
    {
        return isset($this->foreignKeys[$name]);
    }

    /**
     * Checks whether the table has an index.
     *
     * @param string $name The index name.
     * @return bool Whether the table has the index.
     */
    public function hasIndex(string $name): bool
    {
        return isset($this->indexes[$name]);
    }

    /**
     * Returns a table index.
     *
     * @param string $name The index name.
     * @return Index The Index instance.
     *
     * @throws InvalidArgumentException If the index does not exist.
     */
    public function index(string $name): Index
    {
        if (!isset($this->indexes[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table index `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->indexes[$name];
    }

    /**
     * Returns all table indexes.
     *
     * @return array<string, Index> The table indexes keyed by index name.
     */
    public function indexes(): array
    {
        return $this->indexes;
    }

    /**
     * Renames the table.
     *
     * @param string $table The new table name.
     * @return static The Table instance.
     */
    public function rename(string $table): static
    {
        if ($this->schemaTable) {
            $this->newName = $table;
        } else {
            $this->name = $table;
        }

        return $this;
    }

    /**
     * Sets the primary key.
     *
     * @param string|string[] $columns The columns.
     * @return static The Table instance.
     */
    abstract public function setPrimaryKey(array|string $columns): static;

    /**
     * Generates the SQL queries.
     *
     * @return string[] The SQL queries.
     */
    abstract public function sql(): array;

    /**
     * Returns the table data as an array.
     *
     * @return array<string, mixed> The table data.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'comment' => $this->comment,
        ];
    }

    /**
     * Builds a Column.
     *
     * @param string $name The column name.
     * @param array<string, mixed> $data The column data.
     * @return Column The new Column instance.
     */
    abstract protected function buildColumn(string $name, array $data): Column;

    /**
     * Builds a ForeignKey.
     *
     * @param string $name The foreign key name.
     * @param array<string, mixed> $data The foreign key data.
     * @return ForeignKey The new ForeignKey instance.
     */
    protected function buildForeignKey(string $name, array $data): ForeignKey
    {
        return $this->container->build(ForeignKey::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * Builds an Index.
     *
     * @param string $name The index key name.
     * @param array<string, mixed> $data The index data.
     * @return Index The new Index instance.
     */
    protected function buildIndex(string $name, array $data): Index
    {
        return $this->container->build(Index::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * Reloads table data from the schema.
     *
     * @param bool $forceReset Whether to forcefully reload the schema data.
     */
    protected function reloadSchema(bool $forceReset = false): void
    {
        $this->schemaTable = $this->schema->table($this->name);

        if ($forceReset) {
            $this->comment = $this->schemaTable->getComment();
        } else {
            $this->comment ??= $this->schemaTable->getComment();
        }

        $this->columns = $this->schemaTable->columns()
            ->map(fn(SchemaColumn $column): Column => $this->buildColumn(
                $column->getName(),
                array_diff_key($column->toArray(), ['name' => true])
            ))
            ->toArray();

        $this->indexes = $this->schemaTable->indexes()
            ->map(fn(SchemaIndex $index): Index => $this->buildIndex(
                $index->getName(),
                array_diff_key($index->toArray(), ['name' => true])
            ))
            ->toArray();

        $this->foreignKeys = $this->schemaTable->foreignKeys()
            ->map(fn(SchemaForeignKey $foreignKey): ForeignKey => $this->buildForeignKey(
                $foreignKey->getName(),
                array_diff_key($foreignKey->toArray(), ['name' => true])
            ))
            ->toArray();
    }

    /**
     * Changes the order of a column.
     *
     * @param string $name The column name.
     * @param bool $first Whether the column should be moved to the start.
     * @param string|null $after The column to move the new column after.
     */
    protected function updateColumnOrder(string $name, bool $first = false, string|null $after = null): void
    {
        if (!$first && !$after) {
            return;
        }

        $column = $this->columns[$name];
        unset($this->columns[$name]);

        if ($first) {
            $beforeColumns = [];
            $afterColumns = $this->columns;
        } else {
            $afterIndex = array_search($after, array_keys($this->columns), true);

            $beforeColumns = array_slice($this->columns, 0, $afterIndex + 1);
            $afterColumns = array_slice($this->columns, $afterIndex + 1);
        }

        $this->columns = array_merge($beforeColumns, [$name => $column], $afterColumns);
    }
}
