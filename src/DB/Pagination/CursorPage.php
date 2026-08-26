<?php
declare(strict_types=1);

namespace Fyre\DB\Pagination;

use Closure;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Queries\SelectQuery;
use InvalidArgumentException;
use JsonException;
use Override;

use function array_all;
use function array_first;
use function array_intersect_key;
use function array_is_list;
use function array_key_exists;
use function array_keys;
use function array_last;
use function array_map;
use function array_merge;
use function array_pop;
use function array_reverse;
use function base64_decode;
use function base64_encode;
use function count;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;
use function preg_match;
use function rtrim;
use function strtoupper;
use function strtr;

use const JSON_THROW_ON_ERROR;

/**
 * Represents a keyset-paginated page of query results.
 *
 * @template TItem = mixed
 *
 * @extends AbstractPage<TItem>
 */
class CursorPage extends AbstractPage
{
    use MacroTrait;

    protected const CURSOR_FIELD_PREFIX = '__fyre_cursor_';

    protected bool|null $hasNext = null;

    protected bool|null $hasPrevious = null;

    /**
     * @var array<array<bool|float|int|string>>
     */
    protected array $itemValues = [];

    /**
     * @var array<string, 'ASC'|'DESC'>
     */
    protected array $orderBy;

    protected bool $previous = false;

    /**
     * @var array<bool|float|int|string>|null
     */
    protected array|null $values = null;

    /**
     * Constructs a CursorPage.
     *
     * @param SelectQuery<TItem> $query The SelectQuery.
     * @param string|null $cursor The pagination cursor.
     * @param int $perPage The maximum number of items per page.
     */
    public function __construct(
        SelectQuery $query,
        protected string|null $cursor,
        int $perPage
    ) {
        parent::__construct($query, $perPage);

        $this->orderBy = $this->query->getOrderBy()
            |> static::normalizeOrderBy(...);

        if ($this->cursor !== null) {
            [$this->values, $this->previous] = $this->decodeCursor($this->cursor);
        }
    }

    /**
     * Returns the current cursor.
     *
     * @return string|null The current cursor.
     */
    public function currentCursor(): string|null
    {
        return $this->cursor;
    }

    /**
     * Checks whether there is a next page.
     *
     * @return bool Whether there is a next page.
     */
    public function hasNext(): bool
    {
        $this->items();

        return $this->hasNext ?? false;
    }

    /**
     * Checks whether there is a previous page.
     *
     * @return bool Whether there is a previous page.
     */
    public function hasPrevious(): bool
    {
        $this->items();

        return $this->hasPrevious ?? false;
    }

    /**
     * Returns the JSON serialization data.
     *
     * @return array{
     *     data: array<TItem>,
     *     pagination: array{
     *         perPage: int,
     *         nextCursor: string|null,
     *         previousCursor: string|null
     *     }
     * } The serialization data.
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->items(),
            'pagination' => [
                'perPage' => $this->perPage,
                'nextCursor' => $this->nextCursor(),
                'previousCursor' => $this->previousCursor(),
            ],
        ];
    }

    /**
     * Returns the cursor for the next page.
     *
     * @return string|null The next cursor.
     */
    public function nextCursor(): string|null
    {
        if (!$this->hasNext()) {
            return null;
        }

        $values = array_last($this->itemValues);

        return $values === null ?
            null :
            $this->encodeCursor($values, false);
    }

    /**
     * Returns the cursor for the previous page.
     *
     * @return string|null The previous cursor.
     */
    public function previousCursor(): string|null
    {
        if (!$this->hasPrevious()) {
            return null;
        }

        $values = array_first($this->itemValues);

        return $values === null ?
            null :
            $this->encodeCursor($values, true);
    }

    /**
     * Adds the keyset cursor conditions to a query.
     *
     * @param SelectQuery<TItem> $query The SelectQuery.
     */
    protected function applyCursor(SelectQuery $query): void
    {
        if ($this->values === null) {
            return;
        }

        $fields = array_keys($this->orderBy);
        $conditions = $query->expr();
        $lastIndex = count($fields) - 1;

        for ($index = $lastIndex; $index >= 0; $index--) {
            $field = $fields[$index];
            $comparison = $query->expr();
            $greaterThan = ($this->orderBy[$field] === 'ASC') !== $this->previous;

            if ($greaterThan) {
                $comparison->gt($field, $this->values[$index]);
            } else {
                $comparison->lt($field, $this->values[$index]);
            }

            if ($index === $lastIndex) {
                $conditions = $comparison;

                continue;
            }

            $conditions = $query->expr('OR')
                ->add($comparison)
                ->add(
                    $query->expr()
                        ->eq($field, $this->values[$index])
                        ->add($conditions)
                );
        }

        $query->where($conditions);
    }

    /**
     * Decodes and validates a cursor.
     *
     * @param string $cursor The pagination cursor.
     * @return array{array<bool|float|int|string>, bool} The cursor values and direction.
     */
    protected function decodeCursor(string $cursor): array
    {
        $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Cursor is not valid.');
        }

        try {
            $data = json_decode($decoded, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidArgumentException('Cursor is not valid.');
        }

        if (
            !is_array($data) ||
            !isset($data['order'], $data['values'], $data['previous']) ||
            !is_array($data['order']) ||
            !is_array($data['values']) ||
            !array_is_list($data['values']) ||
            !is_bool($data['previous']) ||
            $data['order'] !== $this->orderBy ||
            count($data['values']) !== count($this->orderBy)
        ) {
            throw new InvalidArgumentException('Cursor is not valid.');
        }

        if (!array_all($data['values'], static fn(mixed $value): bool => is_scalar($value))) {
            throw new InvalidArgumentException('Cursor is not valid.');
        }

        return [$data['values'], $data['previous']];
    }

    /**
     * Encodes a cursor from ordered values.
     *
     * @param array<bool|float|int|string> $values The ordered values.
     * @param bool $previous Whether the cursor points to the previous page.
     * @return string The encoded cursor.
     */
    protected function encodeCursor(array $values, bool $previous): string
    {
        $data = json_encode([
            'order' => $this->orderBy,
            'values' => $values,
            'previous' => $previous,
        ], JSON_THROW_ON_ERROR);

        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
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
        $this->applyCursor($query);

        $fields = array_keys($this->orderBy);
        $cursorFields = [];

        foreach ($fields as $index => $field) {
            $alias = static::CURSOR_FIELD_PREFIX.$index;
            $cursorFields[$alias] = $field;
        }

        /** @var array<array<bool|float|int|string>> $itemValues */
        $itemValues = [];

        Closure::bind(function() use ($cursorFields, &$itemValues): void {
            /** @var SelectQuery $this */
            if (array_intersect_key($cursorFields, $this->fields) !== []) {
                throw new InvalidArgumentException('Cursor field aliases conflict with selected fields.');
            }

            $this->fields = array_merge($this->fields, $cursorFields);
            $this->resultCallback = static function(array $row) use ($cursorFields, &$itemValues): array {
                $values = [];

                foreach ($cursorFields as $alias => $field) {
                    if (!array_key_exists($alias, $row)) {
                        throw new InvalidArgumentException('Cursor fields must be present in each database row.');
                    }

                    $value = $row[$alias];
                    unset($row[$alias]);

                    if (!is_scalar($value)) {
                        throw new InvalidArgumentException('Cursor fields must contain non-null scalar values.');
                    }

                    $values[] = $value;
                }

                $itemValues[] = $values;

                return $row;
            };
            $this->dirty();
        }, $query, SelectQuery::class)();

        $orderBy = $this->orderBy;

        if ($this->previous) {
            $orderBy = array_map(
                static fn(string $direction): string => $direction === 'ASC' ? 'DESC' : 'ASC',
                $orderBy
            );
        }

        $items = $query
            ->orderBy($orderBy, true)
            ->limit($this->perPage + 1)
            ->toArray();

        $hasExtra = count($items) > $this->perPage;

        if ($hasExtra) {
            array_pop($items);
            array_pop($itemValues);
        }

        $this->hasNext = $this->previous || $hasExtra;
        $this->hasPrevious = $this->previous ? $hasExtra : $this->cursor !== null;

        if ($this->previous) {
            $items = array_reverse($items);
            $itemValues = array_reverse($itemValues);
        }

        $this->itemValues = $itemValues;

        return $items;
    }

    /**
     * Normalizes and validates an ordering.
     *
     * @param array<string> $orderBy The ordering.
     * @return array<string, 'ASC'|'DESC'> The normalized ordering.
     */
    protected static function normalizeOrderBy(array $orderBy): array
    {
        $normalizedOrderBy = [];

        foreach ($orderBy as $field => $direction) {
            if (is_int($field)) {
                if (
                    !is_string($direction) ||
                    !preg_match('/^([a-z_]\w*(?:\.[a-z_]\w*)*)(?:\s+(ASC|DESC))?\z/i', $direction, $matches)
                ) {
                    throw new InvalidArgumentException('Cursor pagination requires simple ordered fields.');
                }

                $field = $matches[1];
                $direction = $matches[2] ?? 'ASC';
            } else if (!preg_match('/^[a-z_]\w*(?:\.[a-z_]\w*)*\z/i', $field)) {
                throw new InvalidArgumentException('Cursor pagination requires simple ordered fields.');
            }

            $direction = strtoupper($direction);

            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                throw new InvalidArgumentException('Cursor pagination order directions must be ASC or DESC.');
            }

            $normalizedOrderBy[$field] = $direction;
        }

        if ($normalizedOrderBy === []) {
            throw new InvalidArgumentException('Cursor pagination requires an ORDER BY clause.');
        }

        return $normalizedOrderBy;
    }
}
