<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Closure;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

use function array_merge;
use function is_array;

/**
 * Adds WHERE clause support to queries.
 *
 * @phpstan-require-extends Query
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
     * @param array<mixed>|Closure|ConditionExpression|string $conditions The conditions.
     * @param bool $overwrite Whether to overwrite the existing conditions.
     * @return static The Query instance.
     */
    public function where(array|Closure|ConditionExpression|string $conditions, bool $overwrite = false): static
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        if ($overwrite) {
            $this->conditions = $conditions;
        } else {
            $this->conditions = array_merge($this->conditions, $conditions);
        }

        $this->dirty();

        return $this;
    }
}
