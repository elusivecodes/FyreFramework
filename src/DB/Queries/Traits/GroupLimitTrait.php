<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use Fyre\DB\Query;
use InvalidArgumentException;

/**
 * Adds per-group LIMIT/OFFSET support to queries.
 *
 * @phpstan-require-extends Query
 */
trait GroupLimitTrait
{
    /**
     * @var array{field: string, limit: int, offset: int}|null
     */
    protected array|null $groupLimit = null;

    /**
     * Returns the per-group LIMIT configuration.
     *
     * @return array{field: string, limit: int, offset: int}|null The per-group LIMIT configuration.
     */
    public function getGroupLimit(): array|null
    {
        return $this->groupLimit;
    }

    /**
     * Sets the per-group LIMIT and OFFSET clauses.
     *
     * @param int|null $limit The limit, or null to clear the per-group limit.
     * @param string|null $field The partition field.
     * @param int $offset The offset.
     * @return static The Query instance.
     *
     * @throws InvalidArgumentException If the limit, offset or field is not valid.
     */
    public function groupLimit(int|null $limit = null, string|null $field = null, int $offset = 0): static
    {
        if ($limit === null) {
            $this->groupLimit = null;
            $this->dirty();

            return $this;
        }

        if ($limit < 0) {
            throw new InvalidArgumentException('Query group limit must not be negative.');
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('Query group offset must not be negative.');
        }

        if ($field === null || $field === '') {
            throw new InvalidArgumentException('Query group limit field must not be empty.');
        }

        $this->groupLimit = [
            'field' => $field,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $this->dirty();

        return $this;
    }
}
