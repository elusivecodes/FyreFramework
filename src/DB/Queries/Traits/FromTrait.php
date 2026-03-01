<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds FROM clause support to queries.
 */
trait FromTrait
{
    /**
     * Sets the FROM clause.
     *
     * @param array<mixed>|string $table The table.
     * @param bool $overwrite Whether to overwrite the existing table.
     * @return static The Query instance.
     */
    public function from(array|string $table, bool $overwrite = false): static
    {
        return $this->table($table, $overwrite);
    }

    /**
     * Returns the FROM clause.
     *
     * @return array<mixed>|string|null The table.
     */
    public function getFrom(): array|string|null
    {
        return $this->getTable();
    }
}
