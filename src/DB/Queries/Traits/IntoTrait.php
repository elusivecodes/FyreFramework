<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds INSERT INTO clause support to queries.
 */
trait IntoTrait
{
    /**
     * Returns the INTO clause.
     *
     * @return array<mixed>|string|null The table.
     */
    public function getInto(): array|string|null
    {
        return $this->getTable()[0] ?? null;
    }

    /**
     * Sets the INTO clause.
     *
     * @param string $table The table.
     * @param bool $overwrite Whether to overwrite the existing table.
     * @return static The Query instance.
     */
    public function into(string $table, bool $overwrite = false): static
    {
        return $this->table($table, $overwrite);
    }
}
