<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Closure;
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

/**
 * Adds UNION / UNION ALL support to queries.
 *
 * @phpstan-require-extends Query
 */
trait UnionTrait
{
    /**
     * @var array<mixed>[]
     */
    protected array $unions = [];

    /**
     * Adds an EXCEPT query.
     *
     * @param Closure|LiteralExpression|SelectQuery|string $union The query.
     * @param bool $overwrite Whether to overwrite the existing unions.
     * @return static The SelectQuery instance.
     */
    public function except(Closure|LiteralExpression|SelectQuery|string $union, bool $overwrite = false): static
    {
        return $this->union($union, $overwrite, 'except');
    }

    /**
     * Returns the UNION queries.
     *
     * @return array<mixed>[] The UNION queries.
     */
    public function getUnion(): array
    {
        return $this->unions;
    }

    /**
     * Adds an INTERSECT query.
     *
     * @param Closure|LiteralExpression|SelectQuery|string $union The query.
     * @param bool $overwrite Whether to overwrite the existing unions.
     * @return static The SelectQuery instance.
     */
    public function intersect(Closure|LiteralExpression|SelectQuery|string $union, bool $overwrite = false): static
    {
        return $this->union($union, $overwrite, 'intersect');
    }

    /**
     * Adds a UNION DISTINCT query.
     *
     * @param Closure|LiteralExpression|SelectQuery|string $union The query.
     * @param bool $overwrite Whether to overwrite the existing unions.
     * @param string $type The union type.
     * @return static The SelectQuery instance.
     */
    public function union(Closure|LiteralExpression|SelectQuery|string $union, bool $overwrite = false, string $type = 'distinct'): static
    {
        $union = [
            'type' => $type,
            'query' => $union,
        ];

        if ($overwrite) {
            $this->unions = [$union];
        } else {
            $this->unions[] = $union;
        }

        $this->dirty();

        return $this;
    }

    /**
     * Adds a UNION ALL query.
     *
     * @param Closure|LiteralExpression|SelectQuery|string $union The query.
     * @param bool $overwrite Whether to overwrite the existing unions.
     * @return static The SelectQuery instance.
     */
    public function unionAll(Closure|LiteralExpression|SelectQuery|string $union, bool $overwrite = false): static
    {
        return $this->union($union, $overwrite, 'all');
    }
}
