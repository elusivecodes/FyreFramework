<?php
declare(strict_types=1);

namespace Fyre\DB;

use Closure;
use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Iterator;
use OutOfBoundsException;
use Override;

use function array_fill;
use function array_filter;
use function array_last;
use function array_pop;
use function count;
use function sprintf;

/**
 * Buffered iterator over a result set.
 *
 * @template TItem = array<string, mixed>
 *
 * @implements Iterator<int, TItem>
 */
abstract class ResultSet implements Countable, Iterator
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<int, TItem|null>
     */
    protected array $buffer = [];

    protected bool $freed = false;

    protected int $index = 0;

    /**
     * Releases the ResultSet resources.
     */
    public function __destruct()
    {
        $this->free();
    }

    /**
     * Returns all rows.
     *
     * @return array<int, TItem> The rows.
     */
    public function all(): array
    {
        while (!$this->freed) {
            $row = $this->read();

            if ($row === null) {
                $this->free();
                break;
            }

            $this->buffer[] = $row;
        }

        return array_filter(
            $this->buffer,
            static fn(mixed $row): bool => $row !== null
        );
    }

    /**
     * Clears buffered rows.
     *
     * @param int|null $index The row index, or null to clear all rows.
     */
    public function clearBuffer(int|null $index = null): void
    {
        if ($index === null) {
            $count = count($this->buffer);

            $lastRow = null;
            if (!$this->freed && $count > 1) {
                $lastRow = array_pop($this->buffer);
                $count--;
            }

            $this->buffer = array_fill(0, $count, null);

            if ($lastRow !== null) {
                $this->buffer[] = $lastRow;
            }
        } else if (isset($this->buffer[$index])) {
            $this->buffer[$index] = null;
        }
    }

    /**
     * Returns the number of columns.
     *
     * @return int The number of columns.
     */
    abstract public function columnCount(): int;

    /**
     * Returns the column names.
     *
     * @return array<string> The column names.
     */
    abstract public function columns(): array;

    /**
     * Returns the number of rows.
     *
     * @return int The number of rows.
     */
    #[Override]
    public function count(): int
    {
        $this->all();

        return count($this->buffer);
    }

    /**
     * Returns the current row.
     *
     * @return TItem The current row.
     *
     * @throws OutOfBoundsException If the current row index is invalid.
     */
    #[Override]
    public function current(): mixed
    {
        $row = $this->fetch($this->index);

        if ($row === null) {
            throw new OutOfBoundsException(sprintf(
                'Invalid row at index: %s',
                (string) $this->index
            ));
        }

        return $row;
    }

    /**
     * Decorates the result set.
     *
     * @template TDecorated
     *
     * @param Closure(TItem): TDecorated $decorator The decorator callback.
     * @return DecoratedResultSet<TItem, TDecorated> The DecoratedResultSet.
     */
    public function decorate(Closure $decorator): DecoratedResultSet
    {
        return new DecoratedResultSet($this, $decorator);
    }

    /**
     * Returns a row by index.
     *
     * @param int $index The row index.
     * @return TItem|null The row, or null if the index does not exist.
     */
    public function fetch(int $index = 0): mixed
    {
        $bufferIndex = $index - count($this->buffer) + 1;

        while ($bufferIndex-- >= 0 && !$this->freed) {
            $row = $this->read();

            if ($row === null) {
                $this->free();
                break;
            }

            $this->buffer[] = $row;
        }

        return $this->buffer[$index] ?? null;
    }

    /**
     * Returns the first row.
     *
     * @return TItem|null The first row, or null if the ResultSet is empty.
     */
    public function first(): mixed
    {
        $this->rewind();

        return $this->fetch();
    }

    /**
     * Releases the ResultSet resources.
     */
    public function free(): void
    {
        if ($this->freed) {
            return;
        }

        $this->release();
        $this->freed = true;
    }

    /**
     * Returns the Type for a column.
     *
     * @param string $name The column name.
     * @return Type|null The Type, or null if the column does not exist.
     */
    abstract public function getType(string $name): Type|null;

    /**
     * Returns the current row index.
     *
     * @return int The current row index.
     */
    #[Override]
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Returns the last row.
     *
     * @return TItem|null The last row, or null if the ResultSet is empty.
     */
    public function last(): mixed
    {
        $rows = $this->all();

        if ($rows === []) {
            return null;
        }

        return array_last($rows);
    }

    /**
     * Moves to the next row.
     */
    #[Override]
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Resets the current row index.
     */
    #[Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Returns the current row and moves to the next row.
     *
     * @return TItem|null The current row, or null if the ResultSet is exhausted.
     */
    public function row(): mixed
    {
        $row = $this->fetch($this->index);
        $this->index++;

        return $row;
    }

    /**
     * Checks whether the current row index is valid.
     *
     * @return bool TRUE if the current row index is valid, otherwise FALSE.
     */
    #[Override]
    public function valid(): bool
    {
        return $this->fetch($this->index) !== null;
    }

    /**
     * Reads the next row.
     *
     * @return TItem|null The row, or null if the ResultSet is exhausted.
     */
    abstract protected function read(): mixed;

    /**
     * Releases the underlying resources.
     */
    abstract protected function release(): void;
}
