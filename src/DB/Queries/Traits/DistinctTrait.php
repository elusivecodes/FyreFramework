<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds DISTINCT support to SELECT queries.
 */
trait DistinctTrait
{
    protected bool $distinct = false;

    /**
     * Sets the DISTINCT clause.
     *
     * @param bool $distinct Whether to set the DISTINCT clause.
     * @return static The Query instance.
     */
    public function distinct(bool $distinct = true): static
    {
        $this->distinct = $distinct;
        $this->dirty();

        return $this;
    }

    /**
     * Returns the DISTINCT clause.
     *
     * @return bool Whether the DISTINCT clause is enabled.
     */
    public function getDistinct(): bool
    {
        return $this->distinct;
    }
}
