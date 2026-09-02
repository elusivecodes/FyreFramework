<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Closure;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

use function array_merge;
use function is_array;

/**
 * Adds HAVING clause support to queries.
 *
 * @internal
 *
 * @phpstan-require-extends Query
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
     * @param array<mixed>|Closure|ConditionExpression|string $conditions The conditions.
     * @param bool $overwrite Whether to overwrite the existing conditions.
     * @return static The Query instance.
     */
    public function having(array|Closure|ConditionExpression|string $conditions, bool $overwrite = false): static
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        if ($overwrite) {
            $this->having = $conditions;
        } else {
            $this->having = array_merge($this->having, $conditions);
        }

        $this->dirty();

        return $this;
    }
}
