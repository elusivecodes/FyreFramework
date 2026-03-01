<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use function array_merge;

/**
 * Adds HAVING clause support to queries.
 */
trait HavingTrait
{
    /**
     * @var array<mixed>
     */
    protected array $having = [];

    /**
     * Returns the HAVING conditions.
     *
     * @return array<mixed> The HAVING conditions.
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * Sets the HAVING conditions.
     *
     * @param array<mixed>|string $conditions The conditions.
     * @param bool $overwrite Whether to overwrite the existing conditions.
     * @return static The Query instance.
     */
    public function having(array|string $conditions, bool $overwrite = false): static
    {
        $conditions = (array) $conditions;

        if ($overwrite) {
            $this->having = $conditions;
        } else {
            $this->having = array_merge($this->having, $conditions);
        }

        $this->dirty();

        return $this;
    }
}
