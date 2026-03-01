<?php
declare(strict_types=1);

namespace Fyre\Http;

use Closure;
use Countable;
use Fyre\Core\Traits\DebugTrait;
use Iterator;
use OutOfBoundsException;
use Override;
use Psr\Http\Server\MiddlewareInterface;

use function array_splice;
use function array_unshift;
use function count;
use function sprintf;

/**
 * Stores middleware entries (PSR-15 middleware instances, middleware callables, or registry
 * aliases) and provides basic iteration support.
 *
 * @implements Iterator<int, Closure|MiddlewareInterface|string>
 */
class MiddlewareQueue implements Countable, Iterator
{
    use DebugTrait;

    protected int $index = 0;

    /**
     * @var array<Closure|MiddlewareInterface|string>
     */
    protected array $queue = [];

    /**
     * Constructs a MiddlewareQueue.
     *
     * @param array<Closure|MiddlewareInterface|string> $middlewares The middleware.
     */
    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $this->add($middleware);
        }
    }

    /**
     * Adds Middleware.
     *
     * @param Closure|MiddlewareInterface|string $middleware The Middleware.
     * @return static The MiddlewareQueue instance.
     */
    public function add(Closure|MiddlewareInterface|string $middleware): static
    {
        $this->queue[] = $middleware;

        return $this;
    }

    /**
     * Returns the Middleware count.
     *
     * @return int The Middleware count.
     */
    #[Override]
    public function count(): int
    {
        return count($this->queue);
    }

    /**
     * Returns the Middleware at the current index.
     *
     * Note: Unlike the typical {@see Iterator::current()} contract, this method throws when
     * the current index is not valid.
     *
     * @return Closure|MiddlewareInterface|string The Middleware at the current index.
     *
     * @throws OutOfBoundsException If the index is out of bounds.
     */
    #[Override]
    public function current(): Closure|MiddlewareInterface|string
    {
        if (!$this->valid()) {
            throw new OutOfBoundsException(sprintf(
                'Invalid middleware at index: %s',
                (string) $this->index
            ));
        }

        return $this->queue[$this->index];
    }

    /**
     * Inserts Middleware at a specified index.
     *
     * Note: Uses `array_splice()`, so negative indices and indices beyond the array length
     * follow PHP's `array_splice()` semantics.
     *
     * @param int $index The index.
     * @param Closure|MiddlewareInterface|string $middleware The Middleware.
     * @return static The MiddlewareQueue instance.
     */
    public function insertAt(int $index, Closure|MiddlewareInterface|string $middleware): static
    {
        array_splice($this->queue, $index, 0, [$middleware]);

        return $this;
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
     * Advances the index.
     */
    #[Override]
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Prepends Middleware.
     *
     * @param Closure|MiddlewareInterface|string $middleware The Middleware.
     * @return static The MiddlewareQueue instance.
     */
    public function prepend(Closure|MiddlewareInterface|string $middleware): static
    {
        array_unshift($this->queue, $middleware);

        return $this;
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
     * Checks whether the current index is valid.
     *
     * @return bool Whether the current index is valid.
     */
    #[Override]
    public function valid(): bool
    {
        return isset($this->queue[$this->index]);
    }
}
