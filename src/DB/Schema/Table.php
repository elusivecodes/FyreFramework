<?php
declare(strict_types=1);

namespace Fyre\DB\Schema;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Utility\Collection;
use Generator;
use InvalidArgumentException;
use UnitEnum;

use function array_keys;
use function sprintf;

/**
 * Provides schema table metadata and introspection.
 *
 * Provides access to columns, indexes, and foreign keys for a specific table.
 */
abstract class Table
{
    use DebugTrait;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $columns = null;

    /**
     * @var array<string, class-string<UnitEnum>>
     */
    protected array $enumClasses = [];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $foreignKeys = null;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $indexes = null;

    /**
     * @var array<string, Column>
     */
    protected array $loadedColumns = [];

    /**
     * @var array<string, ForeignKey>
     */
    protected array $loadedForeignKeys = [];

    /**
     * @var array<string, Index>
     */
    protected array $loadedIndexes = [];

    /**
     * Constructs a Table.
     *
     * @param Container $container The Container.
     * @param Schema $schema The Schema.
     * @param string $name The table name.
     * @param string|null $comment The table comment.
     */
    public function __construct(
        protected Container $container,
        protected Schema $schema,
        protected string $name,
        protected string|null $comment = null,
    ) {}

    /**
     * Clears the table data (including cache).
     */
    public function clear(): void
    {
        $cache = $this->schema->getCache();

        if ($cache) {
            $prefix = $this->schema->getCachePrefix();
            foreach (['columns', 'indexes', 'foreign_keys'] as $key) {
                $cache->delete($prefix.'.'.$this->name.'.'.$key);
            }
        }

        $this->columns = null;
        $this->indexes = null;
        $this->foreignKeys = null;
        $this->loadedColumns = [];
        $this->loadedIndexes = [];
        $this->loadedForeignKeys = [];
    }

    /**
     * Clears the enum class for a column.
     *
     * @param string $name The column name.
     * @return static The Table instance.
     */
    public function clearEnumClass(string $name): static
    {
        unset($this->enumClasses[$name]);

        if (isset($this->loadedColumns[$name])) {
            $this->loadedColumns[$name]->setEnumClass(null);
        }

        return $this;
    }

    /**
     * Returns a table Column.
     *
     * @param string $name The column name.
     * @return Column The Column instance.
     *
     * @throws InvalidArgumentException If the column does not exist.
     */
    public function column(string $name): Column
    {
        $this->loadColumns();

        if (!isset($this->columns[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->loadedColumns[$name] ??= $this->buildColumn($name, $this->columns[$name])
            ->setEnumClass($this->enumClasses[$name] ?? null);
    }

    /**
     * Returns the names of all table columns.
     *
     * @return string[] The names of all table columns.
     */
    public function columnNames(): array
    {
        $this->loadColumns();

        return array_keys($this->columns ?? []);
    }

    /**
     * Returns all table columns.
     *
     * @return Collection<string, Column> The Collection instance containing the table columns.
     */
    public function columns(): Collection
    {
        $this->loadColumns();

        return new Collection(
            function(): Generator {
                foreach ($this->columns ?? [] as $name => $data) {
                    yield $name => $this->loadedColumns[$name] ??= $this->buildColumn($name, $data)
                        ->setEnumClass($this->enumClasses[$name] ?? null);
                }
            }
        );
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
        $this->loadForeignKeys();

        if (!isset($this->foreignKeys[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table foreign key `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->loadedForeignKeys[$name] ??= $this->buildForeignKey($name, $this->foreignKeys[$name]);
    }

    /**
     * Returns all table foreign keys.
     *
     * @return Collection<string, ForeignKey> The Collection instance containing the table foreign keys.
     */
    public function foreignKeys(): Collection
    {
        $this->loadForeignKeys();

        return new Collection(
            function(): Generator {
                foreach ($this->foreignKeys ?? [] as $name => $data) {
                    yield $name => $this->loadedForeignKeys[$name] ??= $this->buildForeignKey($name, $data);
                }
            }
        );
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
     * Returns the enum class for a column.
     *
     * @param string $name The column name.
     * @return string|null The enum class.
     */
    public function getEnumClass(string $name): string|null
    {
        return $this->enumClasses[$name] ?? null;
    }

    /**
     * Returns the table name.
     *
     * @return string The table name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the Schema.
     *
     * @return Schema The Schema instance.
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Checks whether the table has an auto increment column.
     *
     * @return bool Whether the table has an auto increment column.
     */
    public function hasAutoIncrement(): bool
    {
        $this->loadColumns();

        foreach ($this->columns ?? [] as $data) {
            if ($data['autoIncrement'] ?? false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the table has a column.
     *
     * @param string $name The column name.
     * @return bool Whether the table has the column.
     */
    public function hasColumn(string $name): bool
    {
        $this->loadColumns();

        return isset($this->columns[$name]);
    }

    /**
     * Checks whether a column has an enum class.
     *
     * @param string $name The column name.
     * @return bool Whether the column has an enum class.
     */
    public function hasEnumClass(string $name): bool
    {
        return isset($this->enumClasses[$name]);
    }

    /**
     * Checks whether the table has a foreign key.
     *
     * @param string $name The foreign key name.
     * @return bool Whether the table has the foreign key.
     */
    public function hasForeignKey(string $name): bool
    {
        $this->loadForeignKeys();

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
        $this->loadIndexes();

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
        $this->loadIndexes();

        if (!isset($this->indexes[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Table index `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        return $this->loadedIndexes[$name] ??= $this->buildIndex($name, $this->indexes[$name]);
    }

    /**
     * Returns all table indexes.
     *
     * @return Collection<string, Index> The Collection instance containing the table indexes.
     */
    public function indexes(): Collection
    {
        $this->loadIndexes();

        return new Collection(
            function(): Generator {
                foreach ($this->indexes ?? [] as $name => $data) {
                    yield $name => $this->loadedIndexes[$name] ??= $this->buildIndex($name, $data);
                }
            }
        );
    }

    /**
     * Returns the primary key for the table.
     *
     * @return string[]|null The table primary key.
     */
    public function primaryKey(): array|null
    {
        $this->loadIndexes();

        foreach ($this->indexes ?? [] as $data) {
            if ($data['primary'] ?? false) {
                return $data['columns'] ?? null;
            }
        }

        return null;
    }

    /**
     * Sets the enum class for a column.
     *
     * @param string $name The column name.
     * @param class-string<UnitEnum> $enumClass The enum class.
     * @return static The Table instance.
     */
    public function setEnumClass(string $name, string $enumClass): static
    {
        if (!$this->hasColumn($name)) {
            throw new InvalidArgumentException(sprintf(
                'Table column `%s.%s` does not exist.',
                $this->name,
                $name
            ));
        }

        $this->enumClasses[$name] = $enumClass;

        if (isset($this->loadedColumns[$name])) {
            $this->loadedColumns[$name]->setEnumClass($enumClass);
        }

        return $this;
    }

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
     * @param array<string, mixed> $data The index key data.
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
     * Loads the table columns data.
     */
    protected function loadColumns(): void
    {
        $this->columns ??= $this->schema->load(
            $this->name.'.columns',
            [$this, 'readColumns'](...)
        );
    }

    /**
     * Loads the table foreign keys data.
     */
    protected function loadForeignKeys(): void
    {
        $this->foreignKeys ??= $this->schema->load(
            $this->name.'.foreign_keys',
            [$this, 'readForeignKeys'](...)
        );
    }

    /**
     * Loads the table indexes data.
     */
    protected function loadIndexes(): void
    {
        $this->indexes ??= $this->schema->load(
            $this->name.'.indexes',
            [$this, 'readIndexes'](...)
        );
    }

    /**
     * Reads the table columns data.
     *
     * @return array<string, array<string, mixed>> The table columns data keyed by column name.
     */
    abstract protected function readColumns(): array;

    /**
     * Reads the table foreign keys data.
     *
     * @return array<string, array<string, mixed>> The table foreign keys data keyed by foreign key name.
     */
    abstract protected function readForeignKeys(): array;

    /**
     * Reads the table indexes data.
     *
     * @return array<string, array<string, mixed>> The table indexes data keyed by index name.
     */
    abstract protected function readIndexes(): array;
}
