<?php
declare(strict_types=1);

namespace Fyre\DB;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Iterator;
use OutOfBoundsException;
use Override;
use PDO;
use PDOStatement;

use function array_fill;
use function array_keys;
use function array_last;
use function array_pop;
use function count;
use function sprintf;

/**
 * Buffered iterator over database results.
 *
 * Wraps a {@see PDOStatement}, supports indexed access via an internal buffer, and provides
 * basic column metadata/type helpers for mapping database native types to {@see Type}s.
 *
 * @implements Iterator<int, array<string, mixed>>
 */
abstract class ResultSet implements Countable, Iterator
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, string>
     */
    protected static array $types = [];

    /**
     * @var array<string, mixed>[]
     */
    protected array $buffer = [];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $columnMeta = null;

    protected int|null $count = null;

    protected bool $freed = false;

    protected int $index = 0;

    /**
     * Constructs a ResultSet.
     *
     * @param PDOStatement $result The PDOStatement containing the result set.
     * @param TypeParser $typeParser The TypeParser.
     */
    public function __construct(
        protected PDOStatement $result,
        protected TypeParser $typeParser
    ) {}

    /**
     * Releases the ResultSet resources.
     */
    public function __destruct()
    {
        $this->free();
    }

    /**
     * Returns the results as an array.
     *
     * @return array<string, mixed>[] The buffered results.
     */
    public function all(): array
    {
        $results = $this->result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $this->buffer[] = $result;
        }

        $this->free();

        return $this->buffer;
    }

    /**
     * Clears results from the buffer.
     *
     * @param int|null $index The index.
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

            $this->buffer = array_fill(0, $count, []);

            if ($lastRow !== null) {
                $this->buffer[] = $lastRow;
            }
        } else if (isset($this->buffer[$index])) {
            $this->buffer[$index] = [];
        }
    }

    /**
     * Returns the column count.
     *
     * @return int The column count.
     */
    public function columnCount(): int
    {
        return $this->result->columnCount();
    }

    /**
     * Returns the result columns.
     *
     * @return string[] The result column names.
     */
    public function columns(): array
    {
        return $this->getColumnMeta() |> array_keys(...);
    }

    /**
     * Returns the result count.
     *
     * Note: {@see PDOStatement::rowCount()} is driver-dependent; when it is unreliable this
     * method buffers remaining rows to determine the count.
     *
     * @return int The result count.
     */
    #[Override]
    public function count(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        $rowCount = $this->result->rowCount();

        if ($this->result->columnCount() === 0) {
            $this->free();

            return $this->count = $rowCount;
        }

        if ($rowCount > 0) {
            return $this->count = $rowCount;
        }

        return $this->count = ($this->all() |> count(...));
    }

    /**
     * Returns the result at the current index.
     *
     * @return array<string, mixed> The result at the current index.
     *
     * @throws OutOfBoundsException If the index is out of bounds.
     */
    #[Override]
    public function current(): array
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
     * Returns a result by index.
     *
     * @param int $index The index.
     * @return array<string, mixed>|null The result.
     */
    public function fetch(int $index = 0): array|null
    {
        $bufferIndex = $index - count($this->buffer) + 1;

        while ($bufferIndex-- >= 0) {
            $row = $this->result->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                $this->free();
                break;
            }

            $this->buffer[] = $row;
        }

        return $this->buffer[$index] ?? null;
    }

    /**
     * Returns the first result.
     *
     * @return array<string, mixed>|null The first result.
     */
    public function first(): array|null
    {
        $this->rewind();

        return $this->fetch();
    }

    /**
     * Frees the result from memory.
     */
    public function free(): void
    {
        if ($this->freed) {
            return;
        }

        $this->result->closeCursor();
        $this->freed = true;
    }

    /**
     * Returns a Type for a column.
     *
     * @param string $name The column name.
     * @return Type|null The Type instance.
     */
    public function getType(string $name): Type|null
    {
        $type = $this->getColumnType($name);

        if (!$type) {
            return null;
        }

        return $this->typeParser->use($type);
    }

    /**
     * Returns the current index.
     *
     * @return int The current index.
     */
    #[Override]
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Returns the last result.
     *
     * @return array<string, mixed>|null The last result.
     */
    public function last(): array|null
    {
        $rows = $this->all();

        if ($rows === []) {
            return null;
        }

        return array_last($rows);
    }

    /**
     * Advances the index.
     */
    #[Override]
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Resets the index.
     */
    #[Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Returns the current result.
     *
     * @return array<string, mixed>|null The current result.
     */
    public function row(): array|null
    {
        return $this->fetch($this->index++);
    }

    /**
     * Checks whether the current index is valid.
     *
     * Note: This implementation may advance the underlying statement cursor to populate the
     * buffer when checking validity.
     *
     * @return bool Whether the current index is valid.
     */
    #[Override]
    public function valid(): bool
    {
        return $this->fetch($this->index) !== null;
    }

    /**
     * Returns column metadata.
     *
     * @return array<string, array<string, mixed>> The column metadata keyed by column name.
     */
    protected function getColumnMeta(): array
    {
        if ($this->columnMeta === null) {
            $columnCount = $this->columnCount();

            $this->columnMeta = [];

            for ($i = 0; $i < $columnCount; $i++) {
                $column = $this->result->getColumnMeta($i);

                if (!$column) {
                    continue;
                }

                $name = $column['name'];

                $this->columnMeta[$name] = $column;
            }
        }

        return $this->columnMeta;
    }

    /**
     * Returns the database type for a column.
     *
     * @param string $name The column name.
     * @return string|null The database type.
     */
    protected function getColumnType(string $name): string|null
    {
        $columns = $this->getColumnMeta();
        $column = $columns[$name] ?? null;

        if (!$column) {
            return null;
        }

        $nativeType = $column['native_type'];

        return (string) (static::$types[$nativeType] ?? 'string');
    }
}
