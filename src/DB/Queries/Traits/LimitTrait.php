<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds LIMIT clause support to queries.
 */
trait LimitTrait
{
    protected int|null $limit = null;

    /**
     * Returns the LIMIT clause.
     *
     * @return int|null The LIMIT clause.
     */
    public function getLimit(): int|null
    {
        return $this->limit;
    }

    /**
     * Sets the LIMIT clauses.
     *
     * @param int|null $limit The limit.
     * @return static The Query instance.
     */
    public function limit(int|null $limit = null): static
    {
        $this->limit = $limit;

        $this->dirty();

        return $this;
    }
}
