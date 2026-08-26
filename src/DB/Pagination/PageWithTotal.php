<?php
declare(strict_types=1);

namespace Fyre\DB\Pagination;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Queries\SelectQuery;
use InvalidArgumentException;
use Override;

use function ceil;
use function min;

/**
 * Represents a page of query results with total-count metadata.
 *
 * @template TItem = mixed
 *
 * @extends AbstractPage<TItem>
 */
class PageWithTotal extends AbstractPage
{
    use MacroTrait;

    protected int|null $total = null;

    /**
     * Constructs a PageWithTotal.
     *
     * @param SelectQuery<TItem> $query The SelectQuery.
     * @param int $page The current page number.
     * @param int $perPage The maximum number of items per page.
     */
    public function __construct(
        SelectQuery $query,
        protected int $page,
        int $perPage
    ) {
        if ($this->page < 1) {
            throw new InvalidArgumentException('Page must be greater than zero.');
        }

        parent::__construct($query, $perPage);
    }

    /**
     * Returns the current page number.
     *
     * @return int The current page number.
     */
    public function currentPage(): int
    {
        return $this->page;
    }

    /**
     * Returns the one-based position of the first item on the current page.
     *
     * @return int|null The first item position.
     */
    public function firstItem(): int|null
    {
        if ($this->items() === []) {
            return null;
        }

        return (($this->page - 1) * $this->perPage) + 1;
    }

    /**
     * Checks whether there is a next page.
     *
     * @return bool Whether there is a next page.
     */
    public function hasNext(): bool
    {
        return $this->page < $this->totalPages();
    }

    /**
     * Checks whether there is a previous page.
     *
     * @return bool Whether there is a previous page.
     */
    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    /**
     * Returns the JSON serialization data.
     *
     * @return array{
     *     data: array<TItem>,
     *     pagination: array{
     *         page: int,
     *         perPage: int,
     *         total: int,
     *         totalPages: int
     *     }
     * } The serialization data.
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->items(),
            'pagination' => [
                'page' => $this->page,
                'perPage' => $this->perPage,
                'total' => $this->totalItems(),
                'totalPages' => $this->totalPages(),
            ],
        ];
    }

    /**
     * Returns the one-based position of the last item on the current page.
     *
     * @return int|null The last item position.
     */
    public function lastItem(): int|null
    {
        $firstItem = $this->firstItem();

        if ($firstItem === null) {
            return null;
        }

        return min($firstItem + $this->count() - 1, $this->totalItems());
    }

    /**
     * Returns the next page number.
     *
     * @return int|null The next page number.
     */
    public function nextPage(): int|null
    {
        return $this->hasNext() ?
            $this->page + 1 :
            null;
    }

    /**
     * Returns the previous page number.
     *
     * @return int|null The previous page number.
     */
    public function previousPage(): int|null
    {
        return $this->hasPrevious() ?
            $this->page - 1 :
            null;
    }

    /**
     * Returns the total number of items.
     *
     * @return int The total number of items.
     */
    public function totalItems(): int
    {
        if ($this->total === null) {
            $query = clone $this->query;

            $this->total = $query->count();
        }

        return $this->total;
    }

    /**
     * Returns the total number of pages.
     *
     * @return int The total number of pages.
     */
    public function totalPages(): int
    {
        return (int) ceil($this->totalItems() / $this->perPage);
    }

    /**
     * Loads the page items.
     *
     * @return array<TItem> The page items.
     */
    #[Override]
    protected function loadItems(): array
    {
        $query = clone $this->query;

        return $query
            ->limit($this->perPage, ($this->page - 1) * $this->perPage)
            ->toArray();
    }
}
