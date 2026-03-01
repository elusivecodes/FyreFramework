<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use function array_merge;

/**
 * Adds GROUP BY clause support to queries.
 */
trait GroupByTrait
{
    /**
     * @var string[]
     */
    protected array $groupBy = [];

    /**
     * Returns the GROUP BY fields.
     *
     * @return string[] The GROUP BY fields.
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * Sets the GROUP BY fields.
     *
     * @param string|string[] $fields The fields.
     * @param bool $overwrite Whether to overwrite the existing fields.
     * @return static The Query instance.
     */
    public function groupBy(array|string $fields, bool $overwrite = false): static
    {
        $fields = (array) $fields;

        if ($overwrite) {
            $this->groupBy = $fields;
        } else {
            $this->groupBy = array_merge($this->groupBy, $fields);
        }

        $this->dirty();

        return $this;
    }
}
