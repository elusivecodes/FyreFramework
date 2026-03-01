<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use function array_merge;

/**
 * Adds ORDER BY clause support to queries.
 */
trait OrderByTrait
{
    /**
     * @var array<string>
     */
    protected array $orderBy = [];

    /**
     * Returns the ORDER BY fields.
     *
     * @return array<string> The ORDER BY fields.
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * Sets the ORDER BY fields.
     *
     * @param array<string>|string $fields The fields.
     * @param bool $overwrite Whether to overwrite the existing fields.
     * @return static The Query instance.
     */
    public function orderBy(array|string $fields, bool $overwrite = false): static
    {
        $fields = (array) $fields;

        if ($overwrite) {
            $this->orderBy = $fields;
        } else {
            $this->orderBy = array_merge($this->orderBy, $fields);
        }

        $this->dirty();

        return $this;
    }
}
