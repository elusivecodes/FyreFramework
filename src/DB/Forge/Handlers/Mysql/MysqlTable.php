<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Mysql;

use Fyre\Core\Container;
use Fyre\DB\Forge\Forge;
use Fyre\DB\Forge\Table;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\Handlers\Mysql\MysqlTable as MysqlSchemaTable;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table as SchemaTable;
use Override;

use function array_diff;
use function array_search;
use function array_splice;
use function array_unshift;
use function assert;

/**
 * Builds MySQL table schemas.
 */
class MysqlTable extends Table
{
    /**
     * Constructs a MysqlTable.
     *
     * @param Container $container The Container.
     * @param Forge $forge The Forge.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     * @param string $name The table name.
     * @param string|null $comment The table comment.
     * @param string|null $engine The table engine.
     * @param string|null $charset The table character set.
     * @param string|null $collation The table collation.
     */
    public function __construct(
        Container $container,
        Forge $forge,
        SchemaRegistry $schemaRegistry,
        string $name,
        string|null $comment = null,
        protected string|null $engine = null,
        protected string|null $charset = null,
        protected string|null $collation = null,
    ) {
        parent::__construct(
            $container,
            $forge,
            $schemaRegistry,
            $name,
            $comment
        );

        $connection = $this->forge->getConnection();

        assert($connection instanceof MysqlConnection);

        $this->engine ??= 'InnoDB';
        $this->charset ??= $connection->getCharset();
        $this->collation ??= $connection->getCollation();
    }

    /**
     * {@inheritDoc}
     *
     * Supports the `after` and `first` options for controlling column order.
     */
    #[Override]
    public function addColumn(string $name, array $options = []): static
    {
        $after = $options['after'] ?? null;
        $first = $options['first'] ?? false;

        unset($options['after']);
        unset($options['first']);

        $options['charset'] ??= $this->charset;
        $options['collation'] ??= $this->collation;

        parent::addColumn($name, $options);

        $this->updateColumnOrder($name, $first, $after);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Supports the `after` and `first` options for controlling column order.
     */
    #[Override]
    public function changeColumn(string $name, array $options): static
    {
        $first = $options['first'] ?? false;
        $after = $options['after'] ?? null;

        unset($options['after']);
        unset($options['first']);

        parent::changeColumn($name, $options);

        $this->updateColumnOrder($options['name'] ?? $name, $first, $after);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function compare(SchemaTable $schemaTable): bool
    {
        assert($schemaTable instanceof MysqlSchemaTable);

        return parent::compare($schemaTable) &&
            $this->engine === $schemaTable->getEngine() &&
            $this->charset === $schemaTable->getCharset() &&
            $this->collation === $schemaTable->getCollation();
    }

    /**
     * Returns the table character set.
     *
     * @return string|null The table character set.
     */
    public function getCharset(): string|null
    {
        return $this->charset;
    }

    /**
     * Returns the table collation.
     *
     * @return string|null The table collation.
     */
    public function getCollation(): string|null
    {
        return $this->collation;
    }

    /**
     * Returns the table engine.
     *
     * @return string|null The table engine.
     */
    public function getEngine(): string|null
    {
        return $this->engine;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function setPrimaryKey(array|string $columns): static
    {
        $this->addIndex('PRIMARY', [
            'columns' => (array) $columns,
            'primary' => true,
        ]);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(): array
    {
        $generator = $this->forge->generator();

        assert($generator instanceof MysqlQueryGenerator);

        if (!$this->schemaTable) {
            $query = $generator->buildCreateTable($this);

            return [$query];
        }

        if ($this->dropTable) {
            $query = $generator->buildDropTable($this->name);

            return [$query];
        }

        assert($this->schemaTable instanceof MysqlSchemaTable);

        $originalColumns = $this->schemaTable->columns()->toArray();
        $originalIndexes = $this->schemaTable->indexes()->toArray();
        $originalForeignKeys = $this->schemaTable->foreignKeys()->toArray();

        $statements = [];

        if ($this->engine !== null && $this->engine !== $this->schemaTable->getEngine()) {
            $statements[] = $generator->buildTableEngine($this->engine);
        }

        if ($this->charset !== null && $this->charset !== $this->schemaTable->getCharset()) {
            $statements[] = $generator->buildTableCharset($this->charset);
        }

        if ($this->collation !== null && $this->collation !== $this->schemaTable->getCollation()) {
            $statements[] = $generator->buildTableCollation($this->collation);
        }

        if ($this->comment !== null && $this->comment !== $this->schemaTable->getComment()) {
            $statements[] = $generator->buildTableComment($this->comment);
        }

        foreach ($originalForeignKeys as $name => $foreignKey) {
            if (isset($this->foreignKeys[$name]) && $this->foreignKeys[$name]->compare($foreignKey)) {
                continue;
            }

            $statements[] = $generator->buildDropForeignKey($name);
        }

        foreach ($originalIndexes as $name => $index) {
            if (isset($originalForeignKeys[$name])) {
                continue;
            }

            if (isset($this->indexes[$name]) && $this->indexes[$name]->compare($index)) {
                continue;
            }

            if ($index->isPrimary()) {
                $statements[] = $generator->buildDropPrimaryKey();
            } else {
                $statements[] = $generator->buildDropIndex($name);
            }
        }

        $originalColumnNames = [];
        foreach ($originalColumns as $name => $column) {
            $newName = $this->renameColumns[$name] ?? $name;

            if (isset($this->columns[$newName])) {
                $originalColumnNames[] = $newName;

                continue;
            }

            $statements[] = $generator->buildDropColumn($name);
        }

        $prevColumn = null;
        $columnIndex = 0;

        foreach ($this->columns as $name => $column) {
            $originalName = array_search($name, $this->renameColumns, true) ?: $name;
            $oldIndex = array_search($name, $originalColumnNames, true);

            $options = [];

            if ($oldIndex === false || $columnIndex !== $oldIndex) {
                if ($prevColumn) {
                    $options['after'] = $prevColumn;
                    $originalColumnNames = array_diff($originalColumnNames, [$name]);
                    $prevIndex = array_search($prevColumn, $originalColumnNames, true);
                    array_splice($originalColumnNames, $prevIndex + 1, 0, $name);
                } else {
                    $options['first'] = true;
                    array_unshift($originalColumnNames, $name);
                }
            }

            if (!isset($originalColumns[$originalName])) {
                $statements[] = $generator->buildAddColumn($column, $options);
            } else if ($name !== $originalName || $columnIndex !== $oldIndex || !$column->compare($originalColumns[$originalName])) {
                $options['name'] = $originalName;
                $options['forceComment'] = $column->getComment() !== $originalColumns[$originalName]->getComment();
                $statements[] = $generator->buildChangeColumn($column, $options);
            }

            $prevColumn = $name;
            $columnIndex++;
        }

        foreach ($this->indexes as $name => $index) {
            if (isset($this->foreignKeys[$name])) {
                continue;
            }

            if (isset($originalIndexes[$name]) && $index->compare($originalIndexes[$name])) {
                continue;
            }

            $statements[] = $generator->buildAddIndex($index);
        }

        foreach ($this->foreignKeys as $name => $foreignKey) {
            if (isset($originalForeignKeys[$name]) && $foreignKey->compare($originalForeignKeys[$name])) {
                continue;
            }

            $statements[] = $generator->buildAddForeignKey($foreignKey);
        }

        if ($this->newName && $this->newName !== $this->name) {
            $statements[] = $generator->buildRenameTable($this->newName);
        }

        if ($statements === []) {
            return [];
        }

        $query = $generator->buildAlterTable($this->name, $statements);

        return [$query];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'engine' => $this->engine,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'comment' => $this->comment,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @return MysqlColumn The MysqlColumn instance.
     */
    #[Override]
    protected function buildColumn(string $name, array $data): MysqlColumn
    {
        return $this->container->build(MysqlColumn::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return MysqlIndex The MysqlIndex instance.
     */
    #[Override]
    protected function buildIndex(string $name, array $data): MysqlIndex
    {
        return $this->container->build(MysqlIndex::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * Reloads MySQL-specific table data from the schema.
     *
     * Note: The `forceReset` flag only affects MySQL-specific options (engine/charset/collation). The base
     * table schema (columns/indexes/foreign keys) is always refreshed.
     *
     * @param bool $forceReset Whether to forcefully reload the schema data.
     */
    #[Override]
    protected function reloadSchema(bool $forceReset = false): void
    {
        parent::reloadSchema();

        assert($this->schemaTable instanceof MysqlSchemaTable);

        if ($forceReset) {
            $this->engine = $this->schemaTable->getEngine();
            $this->charset = $this->schemaTable->getCharset();
            $this->collation = $this->schemaTable->getCollation();
        } else {
            $this->engine ??= $this->schemaTable->getEngine();
            $this->charset ??= $this->schemaTable->getCharset();
            $this->collation ??= $this->schemaTable->getCollation();
        }
    }
}
