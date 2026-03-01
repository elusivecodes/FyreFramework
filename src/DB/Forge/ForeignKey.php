<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Schema\ForeignKey as SchemaForeignKey;

/**
 * Defines a table foreign key for DDL operations.
 */
class ForeignKey
{
    use DebugTrait;

    /**
     * @var string[]
     */
    protected array $columns;

    /**
     * @var string[]
     */
    protected array $referencedColumns;

    /**
     * Constructs a ForeignKey.
     *
     * @param Table $table The Table.
     * @param string $name The foreign key name.
     * @param string|string[] $columns The column names.
     * @param string $referencedTable The referenced table name.
     * @param string|string[] $referencedColumns The referenced column names.
     * @param string|null $onUpdate The action on update.
     * @param string|null $onDelete The action on delete.
     */
    public function __construct(
        protected Table $table,
        protected string $name,
        array|string $columns,
        protected string $referencedTable,
        array|string $referencedColumns,
        protected string|null $onUpdate = null,
        protected string|null $onDelete = null
    ) {
        $this->columns = (array) $columns;
        $this->referencedColumns = (array) $referencedColumns;
    }

    /**
     * Checks whether this foreign key is equivalent to a SchemaForeignKey.
     *
     * @param SchemaForeignKey $schemaForeignKey The SchemaForeignKey.
     * @return bool Whether the foreign keys are equivalent.
     */
    public function compare(SchemaForeignKey $schemaForeignKey): bool
    {
        return $this->columns === $schemaForeignKey->getColumns() &&
            $this->referencedTable === $schemaForeignKey->getReferencedTable() &&
            $this->referencedColumns === $schemaForeignKey->getReferencedColumns() &&
            $this->onUpdate === $schemaForeignKey->getOnUpdate() &&
            $this->onDelete === $schemaForeignKey->getOnDelete();
    }

    /**
     * Returns the column names.
     *
     * @return string[] The column names.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the foreign key name.
     *
     * @return string The foreign key name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the delete action.
     *
     * @return string|null The delete action.
     */
    public function getOnDelete(): string|null
    {
        return $this->onDelete;
    }

    /**
     * Returns the update action.
     *
     * @return string|null The update action.
     */
    public function getOnUpdate(): string|null
    {
        return $this->onUpdate;
    }

    /**
     * Returns the referenced column names.
     *
     * @return string[] The referenced column names.
     */
    public function getReferencedColumns(): array
    {
        return $this->referencedColumns;
    }

    /**
     * Returns the referenced table name.
     *
     * @return string The referenced table name.
     */
    public function getReferencedTable(): string
    {
        return $this->referencedTable;
    }

    /**
     * Returns the Table.
     *
     * @return Table The Table instance.
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Returns the foreign key data as an array.
     *
     * @return array<string, mixed> The foreign key data.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'columns' => $this->columns,
            'referencedTable' => $this->referencedTable,
            'referencedColumns' => $this->referencedColumns,
            'onUpdate' => $this->onUpdate,
            'onDelete' => $this->onDelete,
        ];
    }
}
