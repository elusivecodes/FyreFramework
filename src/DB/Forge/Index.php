<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Schema\Index as SchemaIndex;

use function strtolower;

/**
 * Defines a table index for DDL operations.
 */
class Index
{
    use DebugTrait;

    /**
     * @var string[]
     */
    protected array $columns;

    /**
     * Constructs an Index.
     *
     * @param Table $table The Table.
     * @param string $name The index name.
     * @param string|string[] $columns The index columns.
     * @param bool $unique Whether the index is unique.
     * @param bool $primary Whether the index is primary.
     * @param string|null $type The index type.
     */
    public function __construct(
        protected Table $table,
        protected string $name,
        array|string $columns = [],
        protected bool $unique = false,
        protected bool $primary = false,
        protected string|null $type = null,
    ) {
        $this->columns = (array) $columns;

        if ($this->primary) {
            $this->unique = true;
        }

        if ($this->type) {
            $this->type = strtolower($this->type);
        }
    }

    /**
     * Checks whether this index is equivalent to a SchemaIndex.
     *
     * @param SchemaIndex $schemaIndex The SchemaIndex.
     * @return bool Whether the indexes are equivalent.
     */
    public function compare(SchemaIndex $schemaIndex): bool
    {
        return $this->columns === $schemaIndex->getColumns() &&
            $this->unique === $schemaIndex->isUnique() &&
            $this->primary === $schemaIndex->isPrimary() &&
            $this->type === $schemaIndex->getType();

    }

    /**
     * Returns the index columns.
     *
     * @return string[] The index columns.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the index name.
     *
     * @return string The index name.
     */
    public function getName(): string
    {
        return $this->name;
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
     * Returns the index type.
     *
     * @return string|null The index type.
     */
    public function getType(): string|null
    {
        return $this->type;
    }

    /**
     * Checks whether the index is primary.
     *
     * @return bool Whether the index is primary.
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * Checks whether the index is unique.
     *
     * @return bool Whether the index is unique.
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Returns the index data as an array.
     *
     * @return array<string, mixed> The index data.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'columns' => $this->columns,
            'unique' => $this->unique,
            'primary' => $this->primary,
            'type' => $this->type,
        ];
    }
}
