# Promises

`Promise` (`Fyre\Utility\Promise\Promise`) and `AsyncPromise` (`Fyre\Utility\Promise\AsyncPromise`) provide a small, chainable abstraction for deferred results: handle success, handle failure, and compose multiple operations without deeply nested callbacks.

In Fyre, `Promise` settles synchronously during construction, while `AsyncPromise` runs work in a forked child process and is settled once you poll/wait for it.


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [`Promise` model](#promise-model)
- [Environment constraints](#environment-constraints)
- [Method guide](#method-guide)
  - [Chaining (PromiseInterface)](#chaining-promiseinterface)
  - [Creating and converting (Promise)](#creating-and-converting-promise)
  - [Waiting and composition (Promise)](#waiting-and-composition-promise)
  - [Running work in a child process (AsyncPromise)](#running-work-in-a-child-process-asyncpromise)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use promises when you want to represent “a value later” and compose follow-up work with `then()`/`catch()`/`finally()` instead of nested callbacks.

`Promise` is synchronous and best for “wrap this operation and chain handlers”. `AsyncPromise` is for CPU-bound or blocking work you want to run in parallel from a CLI process.

## Quick start

```php
use Fyre\Utility\Promise\Promise;

$promise = new Promise(function(): string {
    return 'ready';
});

$value = $promise
    ->then(static fn(string $value): string => strtoupper($value))
    ->then(static fn(string $value): string => $value.'!');

echo Promise::await($value); // "READY!"
```

```php
use Closure;
use Fyre\Utility\Promise\AsyncPromise;

$promise = new AsyncPromise(function(Closure $resolve, Closure $reject): void {
    $resolve(['pid' => getmypid()]);
});

$result = Promise::await($promise);
```

## `Promise` model

🧠 A promise can settle in one of two ways:

- Fulfilled: produces a value.
- Rejected: produces a rejection reason (`Throwable`).

Chaining happens through `PromiseInterface`:

- `then()` runs an “on fulfilled” handler (and optionally an “on rejected” handler).
- `catch()` runs only when rejected.
- `finally()` runs once settled and preserves the original outcome.

Handlers may return either a plain value or another `PromiseInterface`. When a handler returns a promise, the chain waits for that returned promise to settle before continuing.

To create a promise:

- `Promise` supports a zero-argument callback (the return value becomes the fulfillment value), or a callback that receives `resolve`/`reject` callbacks.
- `AsyncPromise` runs the callback in a child process; the callback should accept `resolve`/`reject` parameters and call exactly one of them.

## Environment constraints

`AsyncPromise` depends on forking and IPC and is only suitable when the runtime allows it:

- Requires `pcntl`, `posix`, and `sockets` extensions.
- Requires a SAPI/environment that supports `pcntl_fork()` (typically CLI on Unix-like systems).
- Long-running CLI workloads are a natural fit, such as a queue [Worker](../queue/worker.md).

If the environment can’t fork or serialize results reliably, prefer `Promise` (synchronous) or push the work into a dedicated process (for example, a queue worker).

## Method guide

Use `Promise::await()` when you want to block and unwrap the final value.

### Chaining (PromiseInterface)

#### **Transform a settled value** (`then()`)

Runs `$onFulfilled` when the promise fulfills and `$onRejected` when it rejects. If the selected callback returns a `PromiseInterface`, it is awaited before the chain continues.

Arguments:
- `$onFulfilled` (`Closure|null`): the fulfillment callback.
- `$onRejected` (`Closure|null`): the rejection callback (receives `Throwable|null`).

```php
$value = Promise::resolve(1)
    ->then(static fn(int $value): int => $value + 1)
    ->then(static fn(int $value): int => $value * 2);
```

#### **Handle rejection** (`catch()`)

Runs `$onRejected` if the promise is rejected. If the callback returns a `PromiseInterface`, it is awaited before the chain continues.

Arguments:
- `$onRejected` (`Closure`): the rejection callback (receives `Throwable|null`).

```php
use Throwable;

$value = Promise::reject()
    ->catch(static function(Throwable|null $reason): string {
        return $reason?->getMessage() ?? 'failed';
    });
```

#### **Run a cleanup callback** (`finally()`)

Runs `$onFinally` once the promise is settled (fulfilled or rejected). The chain resolves/rejects with the original outcome unless the `finally` callback fails (throws or returns a rejected promise).

Arguments:
- `$onFinally` (`Closure`): the settled callback.

```php
$value = Promise::resolve('ok')
    ->finally(static function(): void {
        // cleanup
    });
```

### Creating and converting (Promise)

#### **Wrap a value as a promise** (`Promise::resolve()`)

If `$value` is already a `PromiseInterface`, it is returned as-is. Otherwise, a fulfilled promise is returned for the value.

Arguments:
- `$value` (`mixed`): the value (or promise) to resolve.

```php
$promise = Promise::resolve(123);
```

#### **Create a rejected promise** (`Promise::reject()`)

Creates a rejected promise. If no reason is provided, a `RuntimeException` is used.

Arguments:
- `$reason` (`Throwable|null`): the rejection reason.

```php
use Exception;

$promise = Promise::reject(new Exception('nope'));
```

### Waiting and composition (Promise)

#### **Block for a promise result** (`Promise::await()`)

Returns the fulfillment value, or throws the rejection reason. For `AsyncPromise`, this blocks until the child process settles.

Arguments:
- `$promise` (`PromiseInterface`): the promise to await.

```php
$value = Promise::await(Promise::resolve('done'));
```

#### **Wait for all values** (`Promise::all()`)

Resolves once all items fulfill, producing an array of values using the same keys as the input array. Rejects on the first rejection.

Arguments:
- `$promisesOrValues` (`mixed[]`): Promises or raw values.

```php
$all = Promise::all([
    'a' => Promise::resolve(1),
    'b' => 2,
]);
```

#### **Wait for any successful value** (`Promise::any()`)

Resolves with the first fulfilled value. If every item rejects (or the input array is empty), the returned promise rejects.

Arguments:
- `$promisesOrValues` (`mixed[]`): Promises or raw values.

```php
$any = Promise::any([Promise::reject(), Promise::resolve(3)]);
```

#### **Settle with the first result** (`Promise::race()`)

Resolves or rejects with the first item to settle. If the input array is empty, it fulfills with `null`.

Arguments:
- `$promisesOrValues` (`mixed[]`): Promises or raw values.

```php
$race = Promise::race([Promise::resolve('first'), Promise::resolve('second')]);
```

### Running work in a child process (AsyncPromise)

#### **Wait for settlement** (`AsyncPromise::wait()`)

Blocks the current process until the child process fulfills, rejects, or is cancelled.

```php
$promise = new AsyncPromise(function(Closure $resolve, Closure $reject): void {
    $resolve('ok');
});

$promise->wait();
```

#### **Cancel a pending promise** (`AsyncPromise::cancel()`)

Kills the child process (SIGKILL) and rejects the promise with `CancelledPromiseException`. If the promise has already settled, this does nothing.

Arguments:
- `$message` (`string|null`): an optional cancellation message.

```php
$promise = new AsyncPromise(function(Closure $resolve, Closure $reject): void {
    // ...
});

$promise->cancel('No longer needed');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Handlers run synchronously when a promise settles; there is no event loop or scheduler.
- Unhandled rejections are not silent: a rejected promise can throw its rejection reason when it is destroyed if no rejection handler was attached via `then()`/`catch()`/`finally()`.
- `AsyncPromise` does not settle on its own; you must drive it by calling `wait()`, by calling `Promise::await()`, or by passing it into `Promise::all()`/`any()`/`race()`.
- `AsyncPromise` transfers results over a socket; both fulfillment values and rejection reasons must be serializable.
- `Promise::all()`/`any()`/`race()` poll `AsyncPromise` instances in a tight loop (no sleep). If you need to avoid busy polling, call `wait()`/`await()` on the async promises directly.
- `AsyncPromise` auto-cancels if the child process exceeds its maximum runtime (default 300 seconds) or is stopped, rejecting with `CancelledPromiseException`.

## Related

- [Utilities](index.md)
- [Queue](../queue/index.md)
- [Worker](../queue/worker.md)
