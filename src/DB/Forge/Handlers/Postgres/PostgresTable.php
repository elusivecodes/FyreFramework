<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Postgres;

use Fyre\DB\Forge\Column;
use Fyre\DB\Forge\Table;
use Override;

use function array_merge;
use function array_search;
use function assert;

/**
 * Builds PostgreSQL table schemas.
 */
class PostgresTable extends Table
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function setPrimaryKey(array|string $columns): static
    {
        $this->addIndex($this->name.'_pkey', [
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

        assert($generator instanceof PostgresQueryGenerator);

        $queries = [];

        if (!$this->schemaTable) {
            $queries[] = $generator->buildCreateTable($this);

            foreach ($this->indexes as $name => $index) {
                if ($index->isPrimary() || $index->isUnique() || isset($this->foreignKeys[$name])) {
                    continue;
                }

                $queries[] = $generator->buildCreateIndex($index);
            }

            if ($this->comment) {
                $queries[] = $generator->buildCommentOnTable($this);
            }

            return $queries;
        }

        if ($this->dropTable) {
            $queries[] = $generator->buildDropTable($this->name);

            return $queries;
        }

        $tableName = $this->getName();

        $commentQueries = [];
        $indexQueries = [];
        $statements = [];
        $incrementStatements = [];

        $originalColumns = $this->schemaTable->columns()->toArray();
        $originalIndexes = $this->schemaTable->indexes()->toArray();
        $originalForeignKeys = $this->schemaTable->foreignKeys()->toArray();

        if ($this->name !== $tableName && $this->newName !== null) {
            $sql = $generator->buildRenameTable($this->newName);
            $queries[] = $generator->buildAlterTable($this->name, [$sql]);
        }

        if ($this->comment !== $this->schemaTable->getComment()) {
            $queries[] = $generator->buildCommentOnTable($this);
        }

        foreach ($originalForeignKeys as $name => $foreignKey) {
            if (isset($this->foreignKeys[$name]) && $this->foreignKeys[$name]->compare($foreignKey)) {
                continue;
            }

            $statements[] = $generator->buildDropConstraint($name);
        }

        foreach ($originalIndexes as $name => $index) {
            if (isset($originalForeignKeys[$name])) {
                continue;
            }

            if (isset($this->indexes[$name]) && $this->indexes[$name]->compare($index)) {
                continue;
            }

            if ($index->isPrimary() || $index->isUnique()) {
                $statements[] = $generator->buildDropConstraint($name);
            } else {
                $queries[] = $generator->buildDropIndex($name);
            }
        }

        foreach ($originalColumns as $name => $column) {
            $newName = $this->renameColumns[$name] ?? $name;

            if (isset($this->columns[$newName])) {
                continue;
            }

            $statements[] = $generator->buildDropColumn($name);
        }

        foreach ($this->columns as $name => $column) {
            $originalName = array_search($name, $this->renameColumns, true) ?: $name;

            if (!isset($originalColumns[$originalName])) {
                $statements[] = $generator->buildAddColumn($column);

                if ($column->getComment()) {
                    $commentQueries[] = $generator->buildCommentOnColumn($column);
                }
            } else {
                $originalColumn = $originalColumns[$originalName];

                if ($name !== $originalName) {
                    $sql = $generator->buildRenameColumn($originalName, $name);
                    $queries[] = $generator->buildAlterTable($tableName, [$sql]);
                }

                if (
                    $column->getType() !== $originalColumn->getType() ||
                    $column->getLength() !== $originalColumn->getLength() ||
                    $column->getPrecision() !== $originalColumn->getPrecision() ||
                    $column->getScale() !== $originalColumn->getScale() ||
                    $column->getFractionalSeconds() !== $originalColumn->getFractionalSeconds()
                ) {
                    $statements[] = $generator->buildAlterColumnType($column, [
                        'cast' => $column->getType() !== $originalColumn->getType(),
                    ]);
                }

                if ($column->isNullable() !== $originalColumn->isNullable()) {
                    $statements[] = $generator->buildAlterColumnNullable($column);
                }

                if (!Column::compareDefaultValues($column->getDefault(), $originalColumn->getDefault())) {
                    $statements[] = $generator->buildAlterColumnDefault($column);
                }

                if ($column->isAutoIncrement() !== $originalColumn->isAutoIncrement()) {
                    $incrementStatements[] = $generator->buildAlterColumnAutoIncrement($column);
                }

                if ($column->getComment() !== $originalColumn->getComment()) {
                    $commentQueries[] = $generator->buildCommentOnColumn($column);
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

            if ($index->isPrimary() || $index->isUnique()) {
                $statements[] = $generator->buildAddConstraint($index);
            } else {
                $indexQueries[] = $generator->buildCreateIndex($index);
            }
        }

        foreach ($this->foreignKeys as $name => $foreignKey) {
            if (isset($originalForeignKeys[$name]) && $foreignKey->compare($originalForeignKeys[$name])) {
                continue;
            }

            $statements[] = $generator->buildAddForeignKey($foreignKey);
        }

        $statements = array_merge($statements, $incrementStatements);

        if ($statements !== []) {
            $queries[] = $generator->buildAlterTable($tableName, $statements);
        }

        return array_merge($queries, $indexQueries, $commentQueries);
    }

    /**
     * {@inheritDoc}
     *
     * @return PostgresColumn The PostgresColumn instance.
     */
    #[Override]
    protected function buildColumn(string $name, array $data): PostgresColumn
    {
        return $this->container->build(PostgresColumn::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return PostgresIndex The PostgresIndex instance.
     */
    #[Override]
    protected function buildIndex(string $name, array $data): PostgresIndex
    {
        return $this->container->build(PostgresIndex::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }
}
