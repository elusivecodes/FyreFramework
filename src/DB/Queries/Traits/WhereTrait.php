<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use function array_merge;

/**
 * Adds WHERE clause support to queries.
 */
trait WhereTrait
{
    /**
     * @var array<mixed>
     */
    protected array $conditions = [];

    /**
     * Returns the WHERE conditions.
     *
     * @return array<mixed> The WHERE conditions.
     */
    public function getWhere(): array
    {
        return $this->conditions;
    }

    /**
     * Sets the WHERE conditions.
     *
     * @param array<mixed>|string $conditions The conditions.
     * @param bool $overwrite Whether to overwrite the existing conditions.
     * @return static The Query instance.
     */
    public function where(array|string $conditions, bool $overwrite = false): static
    {
        $conditions = (array) $conditions;

        if ($overwrite) {
            $this->conditions = $conditions;
        } else {
            $this->conditions = array_merge($this->conditions, $conditions);
        }

        $this->dirty();

        return $this;
    }
}
