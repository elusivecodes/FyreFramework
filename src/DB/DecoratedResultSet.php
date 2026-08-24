<?php
declare(strict_types=1);

namespace Fyre\DB;

use Closure;
use Override;

/**
 * Lazily maps the rows of another result set.
 *
 * @template TInput
 * @template TOutput
 *
 * @extends ResultSet<TOutput>
 */
class DecoratedResultSet extends ResultSet
{
    /**
     * Constructs a DecoratedResultSet.
     *
     * @param ResultSet<TInput> $result The wrapped ResultSet.
     * @param Closure(TInput): TOutput $decorator The decorator callback.
     * @param bool $consume Whether to consume buffered rows from the wrapped ResultSet.
     */
    public function __construct(
        protected ResultSet $result,
        protected Closure $decorator,
        protected bool $consume = true
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function clearBuffer(int|null $index = null): void
    {
        parent::clearBuffer($index);

        if (!$this->consume) {
            return;
        }

        $this->result->clearBuffer($index);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function columnCount(): int
    {
        return $this->result->columnCount();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function columns(): array
    {
        return $this->result->columns();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function count(): int
    {
        return $this->result->count();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getType(string $name): Type|null
    {
        return $this->result->getType($name);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function read(): mixed
    {
        $index = $this->result->key();
        $row = $this->result->row();

        if ($row === null) {
            return null;
        }

        $row = ($this->decorator)($row);

        if ($this->consume) {
            $this->result->clearBuffer($index);
        }

        return $row;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function release(): void
    {
        if (!$this->consume) {
            return;
        }

        $this->result->free();
    }
}
