<?php
declare(strict_types=1);

namespace Fyre\DB\Pagination;

use ArrayIterator;
use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Queries\SelectQuery;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Override;
use Traversable;

use function count;

/**
 * Provides common behavior for paginated query results.
 *
 * @template TItem = mixed
 *
 * @implements IteratorAggregate<int, TItem>
 */
abstract class AbstractPage implements Countable, IteratorAggregate, JsonSerializable
{
    use DebugTrait;

    /**
     * @var array<TItem>|null
     */
    protected array|null $items = null;

    /**
     * @var SelectQuery<TItem>
     */
    protected SelectQuery $query;

    /**
     * Constructs an AbstractPage.
     *
     * @param SelectQuery<TItem> $query The SelectQuery.
     * @param int $perPage The maximum number of items per page.
     *
     * @throws InvalidArgumentException If the items per page or query is not valid.
     */
    protected function __construct(SelectQuery $query, protected int $perPage)
    {
        if ($this->perPage < 1) {
            throw new InvalidArgumentException('Items per page must be greater than zero.');
        }

        if ($query->getGroupLimit() !== null) {
            throw new InvalidArgumentException('Query group limits cannot be used with pagination.');
        }

        $this->query = clone $query;
        $this->query->limit(null, 0);
    }

    /**
     * Returns the number of items on the current page.
     *
     * @return int The number of items.
     */
    #[Override]
    public function count(): int
    {
        return $this->items() |> count(...);
    }

    /**
     * Returns an iterator for the page items.
     *
     * @return Traversable<int, TItem> The page item iterator.
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items());
    }

    /**
     * Returns the page items.
     *
     * @return array<TItem> The page items.
     */
    public function items(): array
    {
        return $this->items ??= $this->loadItems();
    }

    /**
     * Returns the maximum number of items per page.
     *
     * @return int The maximum number of items per page.
     */
    public function perPage(): int
    {
        return $this->perPage;
    }

    /**
     * Loads the page items.
     *
     * @return array<TItem> The page items.
     */
    abstract protected function loadItems(): array;
}
