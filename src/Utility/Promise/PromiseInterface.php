<?php
declare(strict_types=1);

namespace Fyre\Utility\Promise;

use Closure;
use Throwable;

/**
 * Defines the promise contract for chaining operations.
 */
interface PromiseInterface
{
    /**
     * Executes a callback if the Promise is rejected.
     *
     * Note: If the callback returns a PromiseInterface, it is awaited before the
     * promise chain continues.
     *
     * @param Closure(Throwable|null): mixed $onRejected The rejected callback.
     * @return PromiseInterface The new Promise instance.
     */
    public function catch(Closure $onRejected): PromiseInterface;

    /**
     * Executes a callback once the Promise is settled.
     *
     * Note: If the callback returns a PromiseInterface, it is awaited before the
     * promise chain continues.
     *
     * @param Closure $onFinally The settled callback.
     * @return PromiseInterface The new Promise instance.
     */
    public function finally(Closure $onFinally): PromiseInterface;

    /**
     * Executes a callback when the Promise is resolved.
     *
     * The fulfilled callback is invoked if the Promise resolves, and the rejected callback
     * is invoked if the Promise is rejected.
     *
     * @param Closure $onFulfilled The fulfilled callback.
     * @param Closure(Throwable|null): mixed $onRejected The rejected callback.
     * @return PromiseInterface The new Promise instance.
     */
    public function then(Closure|null $onFulfilled, Closure|null $onRejected = null): PromiseInterface;
}
