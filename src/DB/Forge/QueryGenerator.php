<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Traits\DebugTrait;

use function implode;
use function strtoupper;

/**
 * DDL SQL generator for {@see Forge} operations.
 */
abstract class QueryGenerator
{
    use DebugTrait;

    /**
     * Constructs a QueryGenerator.
     *
     * @param Forge $forge The Forge.
     */
    public function __construct(
        protected Forge $forge
    ) {}

    /**
     * Generates SQL for adding a column to a table.
     *
     * @param Column $column The Column.
     * @return string The SQL query.
     */
    public function buildAddColumn(Column $column): string
    {
        $sql = 'ADD COLUMN ';
        $sql .= $this->buildColumn($column);

        return $sql;
    }

    /**
     * Generates SQL for altering a table.
     *
     * @param string $table The table name.
     * @param string[] $statements The statements.
     * @return string The SQL query.
     */
    public function buildAlterTable(string $table, array $statements): string
    {
        $sql = 'ALTER TABLE ';
        $sql .= $table;
        $sql .= ' ';
        $sql .= implode(', ', $statements);

        return $sql;
    }

    /**
     * Generates SQL for a column.
     *
     * @param Column $column The Column.
     * @return string The SQL query.
     */
    abstract public function buildColumn(Column $column): string;

    /**
     * Generates SQL for creating a new table.
     *
     * @param Table $table The Table.
     * @param array<string, mixed> $options The table options.
     * @return string The SQL query.
     */
    abstract public function buildCreateTable(Table $table, array $options = []): string;

    /**
     * Generates SQL for dropping a column from a table.
     *
     * @param string $column The column name.
     * @param array<string, mixed> $options The options for dropping the column.
     * @return string The SQL query.
     */
    public function buildDropColumn(string $column, array $options = []): string
    {
        $options['ifExists'] ??= false;

        $sql = 'DROP COLUMN ';

        if ($options['ifExists']) {
            $sql .= 'IF EXISTS ';
        }

        $sql .= $column;

        return $sql;
    }

    /**
     * Generates SQL for dropping an index from a table.
     *
     * @param string $index The index name.
     * @return string The SQL query.
     */
    public function buildDropIndex(string $index): string
    {
        $sql = 'DROP INDEX ';
        $sql .= $index;

        return $sql;
    }

    /**
     * Generates SQL for dropping a table.
     *
     * @param string $table The table name.
     * @param array<string, mixed> $options The options for dropping the table.
     * @return string The SQL query.
     */
    public function buildDropTable(string $table, array $options = []): string
    {
        $options['ifExists'] ??= false;

        $sql = 'DROP TABLE ';

        if ($options['ifExists']) {
            $sql .= 'IF EXISTS ';
        }

        $sql .= $table;

        return $sql;
    }

    /**
     * Generates SQL for a foreign key.
     *
     * @param ForeignKey $foreignKey The ForeignKey.
     * @return string The SQL query.
     */
    public function buildForeignKey(ForeignKey $foreignKey): string
    {
        $onUpdate = $foreignKey->getOnUpdate();
        $onDelete = $foreignKey->getOnDelete();

        $sql = 'CONSTRAINT ';
        $sql .= $foreignKey->getName();
        $sql .= ' FOREIGN KEY ';
        $sql .= '(';
        $sql .= implode(', ', $foreignKey->getColumns());
        $sql .= ')';
        $sql .= ' REFERENCES ';
        $sql .= $foreignKey->getReferencedTable();
        $sql .= ' (';
        $sql .= implode(', ', $foreignKey->getReferencedColumns());
        $sql .= ')';

        if ($onUpdate) {
            $sql .= ' ON UPDATE ';
            $sql .= strtoupper($onUpdate);
        }

        if ($onDelete) {
            $sql .= ' ON DELETE ';
            $sql .= strtoupper($onDelete);
        }

        return $sql;
    }

    /**
     * Generates SQL for renaming a column.
     *
     * @param string $column The column name.
     * @param string $newColumn The new column name.
     * @return string The SQL query.
     */
    public function buildRenameColumn(string $column, string $newColumn): string
    {
        $sql = 'RENAME COLUMN ';
        $sql .= $column;
        $sql .= ' TO ';
        $sql .= $newColumn;

        return $sql;
    }

    /**
     * Generates SQL for renaming a table.
     *
     * @param string $table The new table name.
     * @return string The SQL query.
     */
    public function buildRenameTable(string $table): string
    {
        $sql = 'RENAME TO ';
        $sql .= $table;

        return $sql;
    }
}
