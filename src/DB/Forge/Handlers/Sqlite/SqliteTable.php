<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Sqlite;

use Fyre\DB\Forge\Table;
use Override;
use RuntimeException;

use function array_search;
use function assert;

/**
 * Builds SQLite table schemas.
 */
class SqliteTable extends Table
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function setPrimaryKey(array|string $columns): static
    {
        $this->addIndex('primary', [
            'columns' => (array) $columns,
            'primary' => true,
        ]);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException If a SQL operation cannot be performed.
     */
    #[Override]
    public function sql(): array
    {
        $generator = $this->forge->generator();

        assert($generator instanceof SqliteQueryGenerator);

        $queries = [];

        if (!$this->schemaTable) {
            $queries[] = $generator->buildCreateTable($this);

            foreach ($this->indexes as $name => $index) {
                if ($index->isPrimary() || isset($this->foreignKeys[$name])) {
                    continue;
                }

                $queries[] = $generator->buildCreateIndex($index);
            }

            return $queries;
        }

        if ($this->dropTable) {
            $queries[] = $generator->buildDropTable($this->name);

            return $queries;
        }

        $tableName = $this->getName();

        $originalColumns = $this->schemaTable->columns()->toArray();
        $originalIndexes = $this->schemaTable->indexes()->toArray();
        $originalForeignKeys = $this->schemaTable->foreignKeys()->toArray();

        if ($this->name !== $tableName && $this->newName !== null) {
            $sql = $generator->buildRenameTable($this->newName);
            $queries[] = $generator->buildAlterTable($this->name, [$sql]);
        }

        foreach ($originalForeignKeys as $name => $foreignKey) {
            if (isset($this->foreignKeys[$name]) && $this->foreignKeys[$name]->compare($foreignKey)) {
                continue;
            }

            throw new RuntimeException('Foreign keys cannot be dropped from existing tables.');
        }

        foreach ($originalIndexes as $name => $index) {
            if (isset($originalForeignKeys[$name])) {
                continue;
            }

            if (isset($this->indexes[$name]) && $this->indexes[$name]->compare($index)) {
                continue;
            }

            if ($index->isPrimary()) {
                throw new RuntimeException('Primary keys cannot be dropped from existing tables.');
            }

            $queries[] = $generator->buildDropIndex($name);
        }

        foreach ($originalColumns as $name => $column) {
            $newName = $this->renameColumns[$name] ?? $name;

            if (isset($this->columns[$newName])) {
                continue;
            }

            $alterSql = $generator->buildDropColumn($name);
            $queries[] = $generator->buildAlterTable($tableName, [$alterSql]);
        }

        foreach ($this->columns as $name => $column) {
            $originalName = array_search($name, $this->renameColumns, true) ?: $name;

            if (!isset($originalColumns[$originalName])) {
                $alterSql = $generator->buildAddColumn($column);
                $queries[] = $generator->buildAlterTable($tableName, [$alterSql]);
            } else {
                if (!$column->compare($originalColumns[$originalName])) {
                    throw new RuntimeException('Columns cannot be modified in existing tables.');
                }

                if ($name !== $originalName) {
                    $sql = $generator->buildRenameColumn($originalName, $name);
                    $queries[] = $generator->buildAlterTable($tableName, [$sql]);
                }
            }
        }

        foreach ($this->indexes as $name => $index) {
            if (isset($this->foreignKeys[$name])) {
                continue;
            }

            if (isset($originalIndexes[$name]) && $index->compare($originalIndexes[$name])) {
                continue;
            }

            if ($index->isPrimary()) {
                throw new RuntimeException('Primary keys cannot be added to existing tables.');
            }

            $queries[] = $generator->buildCreateIndex($index);
        }

        foreach ($this->foreignKeys as $name => $foreignKey) {
            if (isset($originalForeignKeys[$name]) && $foreignKey->compare($originalForeignKeys[$name])) {
                continue;
            }

            throw new RuntimeException('Foreign keys cannot be added to existing tables.');
        }

        return $queries;
    }

    /**
     * {@inheritDoc}
     *
     * @return SqliteColumn The new SqliteColumn instance.
     */
    #[Override]
    protected function buildColumn(string $name, array $data): SqliteColumn
    {
        return $this->container->build(SqliteColumn::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return SqliteIndex The new SqliteIndex instance.
     */
    #[Override]
    protected function buildIndex(string $name, array $data): SqliteIndex
    {
        return $this->container->build(SqliteIndex::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }
}
