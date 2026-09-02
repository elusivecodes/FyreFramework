<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Fyre\DB\Query;
use InvalidArgumentException;

/**
 * Adds LIMIT clause support to queries.
 *
 * @internal
 *
 * @phpstan-require-extends Query
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
     *
     * @throws InvalidArgumentException If the limit is not valid.
     */
    public function limit(int|null $limit = null): static
    {
        if ($limit !== null && $limit < 0) {
            throw new InvalidArgumentException('Query limit must not be negative.');
        }

        $this->limit = $limit;

        $this->dirty();

        return $this;
    }
}
