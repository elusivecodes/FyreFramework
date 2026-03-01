<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds WITH (common table expression) support to queries.
 */
trait WithTrait
{
    /**
     * @var array<mixed>[]
     */
    protected array $with = [];

    /**
     * Returns the WITH queries.
     *
     * @return array<mixed>[] The WITH queries.
     */
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * Sets the WITH clause.
     *
     * @param array<mixed> $cte The common table expressions.
     * @param bool $overwrite Whether to overwrite the existing expressions.
     * @param bool $recursive Whether the WITH is recursive.
     * @return static The Query instance.
     */
    public function with(array $cte, bool $overwrite = false, bool $recursive = false): static
    {
        $with = [
            'cte' => $cte,
            'recursive' => $recursive,
        ];

        if ($overwrite) {
            $this->with = [$with];
        } else {
            $this->with[] = $with;
        }

        $this->dirty();

        return $this;
    }

    /**
     * Sets the WITH RECURSIVE clause.
     *
     * @param array<string, mixed> $cte The common table expressions.
     * @param bool $overwrite Whether to overwrite the existing common table expressions.
     * @return static The Query instance.
     */
    public function withRecursive(array $cte, bool $overwrite = false): static
    {
        return $this->with($cte, $overwrite, true);
    }
}
