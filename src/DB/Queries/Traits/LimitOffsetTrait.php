<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds LIMIT/OFFSET clause support to queries.
 */
trait LimitOffsetTrait
{
    protected int|null $limit = null;

    protected int $offset = 0;

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
     * Returns the OFFSET clause.
     *
     * @return int The OFFSET clause.
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Sets the LIMIT and OFFSET clauses.
     *
     * @param int|null $limit The limit.
     * @param int|null $offset The offset.
     * @return static The Query instance.
     */
    public function limit(int|null $limit = null, int|null $offset = null): static
    {
        $this->limit = $limit;

        if ($offset !== null) {
            $this->offset = $offset;
        }

        $this->dirty();

        return $this;
    }

    /**
     * Sets the LIMIT and OFFSET clauses.
     *
     * @param int $offset The offset.
     * @return static The Query instance.
     */
    public function offset(int $offset = 0): static
    {
        $this->offset = $offset;

        $this->dirty();

        return $this;
    }
}
