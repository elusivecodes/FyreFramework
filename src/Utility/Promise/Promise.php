<?php
declare(strict_types=1);

namespace Fyre\Utility\Promise;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Promise\Internal\FulfilledPromise;
use Fyre\Utility\Promise\Internal\RejectedPromise;
use LogicException;
use Override;
use ReflectionFunction;
use RuntimeException;
use Throwable;

/**
 * Provides a promise implementation.
 *
 * Note: Promise callbacks are executed synchronously; handlers are invoked as soon as the
 * promise settles.
 *
 * This implementation does not provide an event loop or scheduler. Promises are expected to
 * settle during callback execution unless you are using AsyncPromise (which is polled).
 */
class Promise implements PromiseInterface
{
    use DebugTrait;
    use MacroTrait;
    use StaticMacroTrait;

    /**
     * @var Closure[]
     */
    protected array $handlers = [];

    protected PromiseInterface|null $result = null;

    /**
     * Waits for all promises to resolve.
     *
     * Resolves with an array of values in the same order as the input.
     * If any promise is rejected, the returned promise is rejected with
     * the first rejection reason.
     *
     * Note: AsyncPromise instances are polled until they settle. Once a rejection occurs,
     * remaining promises are ignored (and AsyncPromise instances are handled with a no-op
     * rejection handler to avoid unhandled rejection errors).
     *
     * @param mixed[] $promisesOrValues The promises or values.
     * @return PromiseInterface The new Promise instance.
     */
    public static function all(array $promisesOrValues): PromiseInterface
    {
        return new Promise(static function(Closure $resolve, Closure $reject) use ($promisesOrValues): void {
            $values = [];
            $rejected = false;

            while ($promisesOrValues !== []) {
                foreach ($promisesOrValues as $i => $promiseOrValue) {
                    if ($rejected) {
                        if ($promiseOrValue instanceof AsyncPromise) {
                            $promiseOrValue->catch(static function(): void {});
                        }

                        unset($promisesOrValues[$i]);

                        continue;
                    }

                    if ($promiseOrValue instanceof AsyncPromise && !$promiseOrValue->poll()) {
                        continue;
                    }

                    Promise::resolve($promiseOrValue)->then(
                        static function(mixed $value = null) use ($i, &$values): void {
                            $values[$i] = $value;
                        },
                        static function(Throwable|null $reason = null) use (&$rejected, $reject): void {
                            $rejected = true;
                            $reject($reason);
                        }
                    );

                    unset($promisesOrValues[$i]);
                }
            }

            if (!$rejected) {
                $resolve($values);
            }
        });
    }

    /**
     * Waits for any promise to resolve.
     *
     * Resolves with the first fulfilled value. If no promises resolve successfully,
     * the returned promise is rejected.
     *
     * Note: AsyncPromise instances are polled until they settle. Once one promise resolves,
     * remaining promises are ignored (and AsyncPromise instances are handled with a no-op
     * rejection handler to avoid unhandled rejection errors).
     *
     * @param mixed[] $promisesOrValues The promises or values.
     * @return PromiseInterface The new Promise instance.
     */
    public static function any(array $promisesOrValues): PromiseInterface
    {
        return new Promise(static function(Closure $resolve, Closure $reject) use ($promisesOrValues): void {
            $resolved = false;

            while ($promisesOrValues !== []) {
                foreach ($promisesOrValues as $i => $promiseOrValue) {
                    if ($resolved) {
                        if ($promiseOrValue instanceof AsyncPromise) {
                            $promiseOrValue->catch(static function(): void {});
                        }

                        unset($promisesOrValues[$i]);

                        continue;
                    }

                    if ($promiseOrValue instanceof AsyncPromise && !$promiseOrValue->poll()) {
                        continue;
                    }

                    Promise::resolve($promiseOrValue)->then(
                        static function(mixed $value = null) use (&$resolved, $resolve): void {
                            $resolved = true;
                            $resolve($value);
                        },
                        static function(): void {}
                    );

                    unset($promisesOrValues[$i]);
                }
            }

            if (!$resolved) {
                $reject(null);
            }
        });
    }

    /**
     * Awaits the result of a Promise.
     *
     * If the promise is rejected, the rejection reason is thrown.
     *
     * Note: AsyncPromise instances are waited on before attaching handlers. For other promise
     * implementations, this method assumes the promise has already settled or will settle
     * synchronously when handlers are attached.
     *
     * @param PromiseInterface $promise The Promise.
     * @return mixed The resolved value.
     *
     * @throws Throwable If the promise is rejected.
     */
    public static function await(PromiseInterface $promise): mixed
    {
        if ($promise instanceof AsyncPromise) {
            $promise->wait();
        }

        $result = null;
        $promise->then(
            static function(mixed $value) use (&$result): void {
                $result = $value;
            },
            static function(Throwable|null $e): void {
                throw $e ?? new RuntimeException();
            }
        );

        return $result;
    }

    /**
     * Waits for the first promise to settle.
     *
     * Resolves or rejects with the result of the first promise that
     * settles. Once one promise settles, all others are ignored.
     *
     * @param mixed[] $promisesOrValues The promises or values.
     * @return PromiseInterface The new Promise instance.
     */
    public static function race(array $promisesOrValues): PromiseInterface
    {
        return new Promise(static function(Closure $resolve, Closure $reject) use ($promisesOrValues): void {
            if ($promisesOrValues === []) {
                $resolve(null);

                return;
            }

            $settled = false;

            while ($promisesOrValues !== []) {
                foreach ($promisesOrValues as $i => $promiseOrValue) {
                    if ($settled) {
                        if ($promiseOrValue instanceof AsyncPromise) {
                            $promiseOrValue->catch(static function(): void {});
                        }

                        unset($promisesOrValues[$i]);

                        continue;
                    }

                    if ($promiseOrValue instanceof AsyncPromise && !$promiseOrValue->poll()) {
                        continue;
                    }

                    Promise::resolve($promiseOrValue)->then($resolve, $reject)->finally(static function() use (&$settled): void {
                        $settled = true;
                    });

                    unset($promisesOrValues[$i]);
                }
            }
        });
    }

    /**
     * Creates a rejected Promise.
     *
     * If no reason is provided, a RuntimeException is used.
     *
     * Note: If a rejected promise is not handled, the rejection reason may be thrown during
     * destruction {@see RejectedPromise}.
     *
     * @param Throwable|null $reason The rejection reason.
     * @return RejectedPromise The new RejectedPromise instance.
     */
    public static function reject(Throwable|null $reason = null): RejectedPromise
    {
        return new RejectedPromise($reason ?? new RuntimeException());
    }

    /**
     * Creates a Promise resolved from a value.
     *
     * If the value is already a PromiseInterface, it is returned as-is.
     * Otherwise, a fulfilled promise is created for the value.
     *
     * @param mixed $value The value to resolve.
     * @return PromiseInterface The resolved Promise instance.
     */
    public static function resolve(mixed $value = null): PromiseInterface
    {
        if ($value instanceof PromiseInterface) {
            return $value;
        }

        return new FulfilledPromise($value);
    }

    /**
     * Constructs a Promise.
     *
     * @param Closure $callback The Promise callback.
     */
    public function __construct(Closure $callback)
    {
        $this->call($callback);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function catch(Closure $onRejected): PromiseInterface
    {
        return $this->then(null, $onRejected);
    }

    /**
     * {@inheritDoc}
     *
     * The callback is run whether the Promise is fulfilled or rejected.
     * The returned promise resolves or rejects with the original result.
     */
    #[Override]
    public function finally(Closure $onFinally): PromiseInterface
    {
        return $this->then(
            static fn(mixed $value): PromiseInterface => Promise::resolve($onFinally())
                ->then(static fn(): mixed => $value),
            static fn(Throwable|null $reason): PromiseInterface => Promise::resolve($onFinally())
                ->then(static fn(): RejectedPromise => static::reject($reason))
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function then(Closure|null $onFulfilled, Closure|null $onRejected = null): PromiseInterface
    {
        if ($this->result) {
            return $this->result->then($onFulfilled, $onRejected);
        }

        return new Promise(
            function(Closure $resolve, Closure $reject) use ($onFulfilled, $onRejected): void {
                $this->handlers[] = static function(PromiseInterface $promise) use ($resolve, $reject, $onFulfilled, $onRejected): void {
                    $promise = $promise->then($onFulfilled, $onRejected);

                    if ($promise instanceof Promise && $promise->result) {
                        $promise->handlers[] = static function(PromiseInterface $promise) use ($resolve, $reject): void {
                            $promise->then($resolve, $reject);
                        };
                    } else {
                        $promise->then($resolve, $reject);
                    }
                };
            }
        );
    }

    /**
     * Calls the Promise callback.
     *
     * @param Closure $callback The Promise callback.
     */
    protected function call(Closure $callback): void
    {
        $reflection = new ReflectionFunction($callback);
        $paramCount = $reflection->getNumberOfParameters();

        try {
            if ($paramCount === 0) {
                $value = $callback();
                $this->settle(static::resolve($value));
            } else {
                $target = & $this;

                $callback(
                    static function(mixed $value = null) use (&$target): void {
                        if (!$target) {
                            return;
                        }

                        $target->settle(static::resolve($value));
                        $target = null;
                    },
                    function(Throwable|null $reason = null) use (&$target): void {
                        if (!$target || $target->result) {
                            return;
                        }

                        $target = null;

                        $this->settle(static::reject($reason));
                    }
                );
            }
        } catch (Throwable $e) {
            $this->settle(static::reject($e));
        }
    }

    /**
     * Settles the resulting Promise.
     *
     * @param PromiseInterface $result The Promise.
     *
     * @throws LogicException If a promise is resolved with itself.
     */
    protected function settle(PromiseInterface $result): void
    {
        if ($this->result) {
            throw new LogicException('Cannot resolve a promise that has already settled.');
        }

        while ($result instanceof self && $result->result) {
            $result = $result->result;
        }

        if ($result === $this) {
            $result = static::reject(new LogicException('Cannot resolve a promise with itself.'));
        }

        $handlers = $this->handlers;

        $this->handlers = [];
        $this->result = $result;

        foreach ($handlers as $handle) {
            $handle($result);
        }
    }
}
