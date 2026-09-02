<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Fyre\DB\Query;
use InvalidArgumentException;

use function array_key_exists;
use function array_merge;
use function is_array;
use function is_numeric;
use function is_string;
use function sprintf;

/**
 * Adds JOIN clause support to queries.
 *
 * @internal
 *
 * @phpstan-require-extends Query
 */
trait JoinTrait
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $joins = [];

    /**
     * Returns the JOIN tables.
     *
     * @return array<string, array<string, mixed>> The JOIN tables keyed by alias.
     */
    public function getJoin(): array
    {
        return $this->joins;
    }

    /**
     * Sets the JOIN tables.
     *
     * @param array<array<string, mixed>> $joins The joins.
     * @param bool $overwrite Whether to overwrite the existing joins.
     * @return static The Query instance.
     */
    public function join(array $joins, bool $overwrite = false): static
    {
        $joins = static::normalizeJoins($joins);

        if ($overwrite) {
            $this->joins = $joins;
        } else {
            $this->joins = array_merge($this->joins, $joins);
        }

        $this->dirty();

        return $this;
    }

    /**
     * Normalizes JOIN conditions.
     *
     * @param mixed $conditions The conditions.
     * @return array<mixed> The normalized conditions.
     */
    protected static function normalizeJoinConditions(mixed $conditions): array
    {
        if ($conditions === null) {
            return [];
        }

        if (!is_array($conditions)) {
            return [$conditions];
        }

        return $conditions;
    }

    /**
     * Normalizes a joins array.
     *
     * @param array<array<string, mixed>> $joins The joins.
     * @return array<string, array<string, mixed>> The normalized joins keyed by alias.
     *
     * @throws InvalidArgumentException If an alias is not a string.
     */
    protected static function normalizeJoins(array $joins): array
    {
        $results = [];
        foreach ($joins as $alias => $join) {
            if (is_numeric($alias)) {
                $alias = $join['alias'] ?? $join['table'] ?? null;
            }

            if (!is_string($alias)) {
                throw new InvalidArgumentException(sprintf(
                    'Join alias `%s` must be a string.',
                    $alias
                ));
            }

            $join['table'] ??= $alias;

            if (array_key_exists('conditions', $join)) {
                $join['conditions'] = static::normalizeJoinConditions($join['conditions']);
            }

            $results[$alias] = $join;
        }

        return $results;
    }
}
