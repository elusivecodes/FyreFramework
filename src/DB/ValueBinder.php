<?php
declare(strict_types=1);

namespace Fyre\DB;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use Override;

use function count;

/**
 * Generates named placeholders and stores bound values for prepared statements.
 */
class ValueBinder implements Countable
{
    use DebugTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $bindings = [];

    /**
     * Binds a value.
     *
     * @param mixed $value The value to bind.
     * @return string The parameter placeholder.
     */
    public function bind(mixed $value): string
    {
        $nextKey = 'p'.$this->count();

        $this->bindings[$nextKey] = $value;

        return ':'.$nextKey;
    }

    /**
     * Returns the bound values.
     *
     * @return array<string, mixed> The bound values keyed by placeholder name (without the leading `:`).
     */
    public function bindings(): array
    {
        return $this->bindings;
    }

    /**
     * Returns the number of bound values.
     *
     * @return int The number of bound values.
     */
    #[Override]
    public function count(): int
    {
        return count($this->bindings);
    }
}
