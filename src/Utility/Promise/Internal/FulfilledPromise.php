<?php
declare(strict_types=1);

namespace Fyre\Utility\Promise\Internal;

use Closure;
use Fyre\Utility\Promise\Promise;
use Fyre\Utility\Promise\PromiseInterface;
use Override;
use Throwable;

/**
 * Represents a fulfilled promise.
 *
 * Fulfilled promises invoke fulfillment handlers immediately and ignore rejection handlers.
 */
class FulfilledPromise implements PromiseInterface
{
    /**
     * Constructs a FulfilledPromise.
     *
     * @param mixed $value The resolved value.
     */
    public function __construct(
        protected mixed $value
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function catch(Closure $onRejected): PromiseInterface
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function finally(Closure $onFinally): PromiseInterface
    {
        return $this->then(
            static fn(mixed $value): PromiseInterface => Promise::resolve($onFinally())
                ->then(static fn(): mixed => $value)
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function then(Closure|null $onFulfilled, Closure|null $onRejected = null): PromiseInterface
    {
        if ($onFulfilled === null) {
            return $this;
        }

        try {
            return Promise::resolve($onFulfilled($this->value));
        } catch (Throwable $e) {
            return new RejectedPromise($e);
        }
    }
}
