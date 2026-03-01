<?php
declare(strict_types=1);

namespace Fyre\DB\Schema;

use Fyre\Core\Traits\DebugTrait;

/**
 * Represents schema foreign key metadata.
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
     * @param string[] $columns The column names.
     * @param string $referencedTable The referenced table name.
     * @param string[] $referencedColumns The referenced column names.
     * @param string|null $onUpdate The action on update.
     * @param string|null $onDelete The action on delete.
     */
    public function __construct(
        protected Table $table,
        protected string $name,
        array $columns = [],
        protected string|null $referencedTable = null,
        array $referencedColumns = [],
        protected string|null $onUpdate = null,
        protected string|null $onDelete = null
    ) {
        $this->columns = $columns;
        $this->referencedColumns = $referencedColumns;
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
     * @return string|null The referenced table name.
     */
    public function getReferencedTable(): string|null
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
