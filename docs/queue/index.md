# Queue

🧭 Queue covers background jobs, message delivery constraints (delay/expiry/retries/uniqueness), handler configuration, and workers that execute jobs through the container.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Mental model](#mental-model)
- [Configuring queue handlers](#configuring-queue-handlers)
  - [Base handler options](#base-handler-options)
  - [Example configuration](#example-configuration)
- [Selecting a handler](#selecting-a-handler)
- [Building one-off handlers](#building-one-off-handlers)
- [Built-in queue handlers](#built-in-queue-handlers)
  - [Redis](#redis)
- [Writing jobs](#writing-jobs)
- [Pushing jobs](#pushing-jobs)
  - [Message options](#message-options)
- [Processing jobs](#processing-jobs)
- [Inspecting queues](#inspecting-queues)
- [Lifecycle events](#lifecycle-events)
- [Method guide](#method-guide)
  - [`QueueManager`](#queuemanager)
  - [`Queue`](#queue-1)
  - [`Message`](#message)
  - [`RedisQueue`](#redisqueue)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Queue handlers are a good fit when you need to:

- run potentially slow work outside the main request/command flow
- delay work until later (for example, “send in 5 minutes”)
- retry work that can fail transiently (network calls, rate limits)
- enforce uniqueness so the same job doesn’t enqueue repeatedly

## Quick start

📌 A typical queue workflow looks like:

1) Configure a queue handler in your app config (see [Configuring queue handlers](#configuring-queue-handlers)).
2) Write a job class with a `run()` method (see [Writing jobs](#writing-jobs)).
3) Push jobs with `QueueManager::push()` (see [Pushing jobs](#pushing-jobs)).
4) Run a worker to process jobs (see [Queue Worker](worker.md) and [Built-in Console Commands](../console/commands.md#queueworker)).

Example job and enqueue:

```php
class SendWelcomeEmailJob
{
    public function run(Mailer $mailer, string $email): void
    {
        $mailer->sendWelcome($email);
    }
}

$queues = app(\Fyre\Queue\QueueManager::class);
$email = 'user@example.com';

$queues->push(SendWelcomeEmailJob::class, ['email' => $email]);
```

## Mental model

🧠 The queue subsystem is built around four core types:

- `QueueManager` loads handler configurations from [Config](../core/config.md) (the `Queue` key) and provides shared handler instances by config key.
- `Queue` is the handler contract. Handlers decide how messages are stored and how retries/uniqueness are enforced.
- `Message` is a job payload plus delivery constraints (delay/expiry, retries, uniqueness).
- `Worker` polls a handler, executes messages, and dispatches lifecycle events.

A “job” is represented by a class name and method name stored on the message (`className` + `method`), plus an `arguments` array passed to the container call.

The worker executes jobs via `Container::call()`, which means:

- message arguments can be passed by **parameter name** (recommended) or positionally
- any remaining parameters can be resolved by **type-hint** through the container

## Configuring queue handlers

Queue handler configuration is read from the `Queue` key in your config (see [Config](../core/config.md)). Each named config entry is an options array passed to `QueueManager::build()`.

### Base handler options

These options apply to all queue handlers:

- `className` (`class-string`) — handler class name (must extend `Queue`).

Other options depend on the selected handler.

### Example configuration

```php
use Fyre\Queue\Handlers\RedisQueue;

return [
    'Queue' => [
        'default' => [
            'className' => RedisQueue::class,
            'host' => '127.0.0.1',
            'port' => 6379,
        ],
    ],
];
```

## Selecting a handler

Use a config key to select which stored handler config to use. When no key is provided, `QueueManager::DEFAULT` (`default`) is used.

```php
use Fyre\Queue\QueueManager;

function queueStats(QueueManager $queues): array
{
    $default = $queues->use();

    return $default->stats();
}
```

📌 Note: There are two common “routing” settings and they solve different problems:

- `config` selects *which handler configuration* to use (for example, which Redis connection).
- `queue` selects *which logical queue* inside that handler to use (for example, `emails` vs `search`).

You can set both when enqueueing:

```php
$queues->push(SendWelcomeEmailJob::class, ['email' => $email], [
    'config' => 'default',
    'queue' => 'emails',
]);
```

## Building one-off handlers

Use `build()` to construct a handler directly from options without storing it under a key (and without sharing it).

```php
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\QueueManager;

function connectToAltRedis(QueueManager $queues): void
{
    $temp = $queues->build([
        'className' => RedisQueue::class,
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 2,
    ]);

    $temp->reset();
}
```

## Built-in queue handlers

The framework ships built-in handlers under `Fyre\Queue\Handlers\*`.

### Redis

Queue handler backed by Redis.

📌 Note: `RedisQueue` requires the `redis` PHP extension (phpredis).

⚠️ Security note: `RedisQueue` stores `Message` objects using PHP serialization. Treat your Redis instance as trusted infrastructure (restrict network access, require auth/TLS as appropriate). If an attacker can write to Redis, they may be able to inject malicious serialized payloads.

Options:

- `host` (`string`) — Redis host (default: `127.0.0.1`)
- `port` (`int`) — Redis port (default: `6379`)
- `password` (`string|null`) — Redis password (default: `null`)
- `database` (`int|null`) — Redis database index (default: `null`)
- `timeout` (`int`) — connection timeout in seconds (default: `0`)
- `persist` (`bool`) — use a persistent connection (default: `true`)
- `tls` (`bool`) — connect using `tls://` (default: `false`)
- `ssl` (`array`) — TLS client settings (all default to `null`):
  - `key` (`string|null`)
  - `cert` (`string|null`)
  - `ca` (`string|null`)

## Writing jobs

Jobs are plain classes with a method (default `run`) that the worker can call.

When writing jobs:

- Prefer **named arguments** when pushing jobs (`['reportId' => 123]`) so the call stays stable if you reorder parameters.
- Job methods can also type-hint services; the container resolves those automatically.

📌 Note: A job is treated as failed when it returns `false` or throws an exception. Any other return value is treated as success.

```php
class GenerateReportJob
{
    public function run(ReportService $reports, int $reportId): void
    {
        $reports->generate($reportId);
    }
}

$queues->push(GenerateReportJob::class, ['reportId' => $reportId]);
```

## Pushing jobs

Use `QueueManager::push()` to enqueue a job class + method call. The default message method is `run`.

```php
$queues->push(SearchIndexJob::class, ['postId' => $postId], [
    // Queue name inside the handler (use this to separate workloads).
    'queue' => 'search',

    // Delay execution until later.
    'delay' => 60,

    // Prevent enqueuing duplicates (handler-dependent).
    'unique' => true,
]);
```

To call a non-`run` method, set the `method` option:

```php
$queues->push(CacheWarmupJob::class, ['userId' => $userId], [
    'method' => 'handle',
]);
```

### Message options

Options are stored on the message and interpreted by the worker and/or the handler:

- `className` (`class-string`) — job class name (set by `QueueManager::push()`)
- `arguments` (`array<string, mixed>`) — job arguments (set by `QueueManager::push()`)
- `method` (`string`) — job method name (default: `run`)
- `config` (`string`) — queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`) — queue name inside the handler (default: `Queue::DEFAULT`)
- `delay` (`int`) — seconds to delay before becoming ready (converted to `after` at construction)
- `expires` (`int`) — seconds until the message expires (converted to `before` at construction)
- `after` (`int|null`) — absolute ready timestamp (seconds since epoch)
- `before` (`int|null`) — absolute expiry timestamp (seconds since epoch)
- `retry` (`bool`) — whether retries are allowed (default: `true`)
- `maxRetries` (`int`) — maximum retry attempts (default: `5`)
- `unique` (`bool`) — whether the handler should enforce uniqueness (default: `false`)

📌 Note: If you pass both `delay` and `after`, `after` takes precedence. If you pass both `expires` and `before`, `before` takes precedence.

## Processing jobs

Messages are processed by a `Worker`, which repeatedly calls `Queue::pop()`, then executes the job via the container and marks it as `complete()` or `fail()`.

For the runtime loop, worker options, and operational guidance, see [Queue Worker](worker.md).

📌 Note: Queues are designed for at-least-once processing. A job may run more than once (for example, due to retries or crashes). Prefer idempotent job design (safe to run multiple times).

## Inspecting queues

Use `Queue::stats()` to inspect the current queue state. For `RedisQueue`, stats include counts for `queued`, `delayed`, `completed`, `failed`, and `total`.

```php
$queues = app(\Fyre\Queue\QueueManager::class);

$queue = $queues->use();
$queueNames = $queue->queues();
$stats = $queue->stats('search');
```

You can also view stats from the CLI using `queue:stats` (see [Built-in Console Commands](../console/commands.md#queuestats)).

Run it via argv parsing:

```php
$commandRunner->handle(['app', 'queue:stats']);
$commandRunner->handle(['app', 'queue:stats', '--config', 'default', '--queue', 'search']);
```

## Lifecycle events

The worker dispatches queue lifecycle events through the event system:

- `Queue.start` — `message`
- `Queue.success` — `message`
- `Queue.failure` — `message`, `shouldRetry`
- `Queue.exception` — `message`, `exception`, `shouldRetry`
- `Queue.invalid` — `message`

For event listening and handler patterns, see [Events](../events/index.md).

## Method guide

This section focuses on the methods you’ll use most when configuring handlers, pushing jobs, and building custom queue workflows.

### `QueueManager`

#### **Push a job** (`push()`)

Queue a job as a class + method call. This builds a `Message` from the supplied `$arguments` and `$options`, selects the handler via the message `config` option, and enqueues the message.

Arguments:
- `$className` (`class-string`): job class name.
- `$arguments` (`array<string, mixed>`): arguments passed to the container call.
- `$options` (`array<string, mixed>`): message options (queue, delay, expires, retry, uniqueness, etc.).

```php
$queues->push(GenerateReportJob::class, ['reportId' => 123], [
    'method' => 'run',
    'queue' => 'default',
    'delay' => 10,
    'unique' => true,
]);
```

#### **Use a configured handler** (`use()`)

Get a shared handler instance for a config key (building it on first use).

Arguments:
- `$key` (`string`): handler config key (defaults to `QueueManager::DEFAULT`).

```php
$queue = $queues->use();
$queued = $queue->stats()['queued'];
```

#### **Build a one-off handler** (`build()`)

Build a handler from an options array without storing or sharing it under a config key.

Arguments:
- `$options` (`array<string, mixed>`): handler options; must include `className` for a class extending `Queue`.

```php
$tempQueue = $queues->build([
    'className' => RedisQueue::class,
    'host' => '127.0.0.1',
    'port' => 6379,
]);

$tempQueue->reset();
```

#### **Set handler config** (`setConfig()`)

Register a handler configuration under a key. Keys are write-once: calling this method with an existing key throws an exception.

Arguments:
- `$key` (`string`): config key.
- `$options` (`array<string, mixed>`): handler options (including `className`).

```php
$queues->setConfig('reports', [
    'className' => RedisQueue::class,
    'host' => '127.0.0.1',
    'port' => 6379,
    'database' => 2,
]);
```

#### **Read handler config** (`getConfig()`)

Get all handler configs or a single config by key.

Arguments:
- `$key` (`string|null`): config key, or `null` to return all configs.

```php
$allConfigs = $queues->getConfig();
$defaultConfig = $queues->getConfig('default');
```

#### **Check whether a config exists** (`hasConfig()`)

`hasConfig()` checks whether a config exists.

Arguments:
- `$key` (`string`): config key (defaults to `QueueManager::DEFAULT`).

```php
$exists = $queues->hasConfig();
```

#### **Check whether a handler is loaded** (`isLoaded()`)

`isLoaded()` checks whether a shared handler instance has been built for that key.

Arguments:
- `$key` (`string`): config key (defaults to `QueueManager::DEFAULT`).

```php
$loaded = $queues->isLoaded();
```

#### **Unload a handler** (`unload()`)

Remove a handler instance and its config entry.

Arguments:
- `$key` (`string`): config key (defaults to `QueueManager::DEFAULT`).

```php
$queues->unload('reports');
```

#### **Clear all configs and instances** (`clear()`)

Remove all handler configs and all shared instances from the manager.

```php
$queues->clear();
```

### `Queue`

`Queue` is an abstract base class that defines the public contract a handler must implement.

#### **Push a message** (`push()`)
Add a message to the handler. Returns `false` when the handler declines to enqueue the message (for example, because it is expired or violates uniqueness constraints).

Arguments:
- `$message` (`Message`): message to enqueue.

```php
$message = new Message([
    'className' => GenerateReportJob::class,
    'method' => 'run',
    'arguments' => ['reportId' => 123],
]);

$ok = $queue->push($message);
```

#### **Pop the next message** (`pop()`)

Pop the next ready message from a named queue. Returns `null` when the queue is empty.

Arguments:
- `$queue` (`string`): queue name (defaults to `Queue::DEFAULT`).

```php
$message = $queue->pop();

if ($message) {
    $queue->complete($message);
}
```

#### **Mark completion** (`complete()`)

Mark a message as successfully processed.

Arguments:
- `$message` (`Message`): processed message.

```php
$queue->complete($message);
```

#### **Mark failure and retry** (`fail()`)

Mark a message as failed and optionally retry it. The handler decides how retries are implemented and returns whether the message was retried.

Arguments:
- `$message` (`Message`): failed message.

```php
$shouldRetry = $queue->fail($message);
```

#### **List queues** (`queues()`)

Return the set of active queue names known to the handler.

```php
$queueNames = $queue->queues();
```

#### **Queue stats** (`stats()`)

Return queue statistics for a named queue.

Arguments:
- `$queue` (`string`): queue name (defaults to `Queue::DEFAULT`).

```php
$stats = $queue->stats('default');
```

#### **Clear a queue** (`clear()`)

`clear()` removes all items from a queue.

Arguments:
- `$queue` (`string`): queue name (defaults to `Queue::DEFAULT`).

```php
$queue->clear('default');
```

#### **Reset queue statistics** (`reset()`)

`reset()` resets a queue’s statistics.

Arguments:
- `$queue` (`string`): queue name (defaults to `Queue::DEFAULT`).

```php
$queue->reset('default');
```

### `Message`

#### **Validate a message** (`isValid()`)

Check whether the target class exists and the configured method exists on that class. Invalid messages are skipped by the worker and emit `Queue.invalid`.

```php
$ok = $message->isValid();
```

#### **Check readiness** (`isReady()`)

Check whether a message is ready to run.

```php
$ready = $message->isReady();
```

#### **Check expiry** (`isExpired()`)

Check whether a message has expired.

```php
$expired = $message->isExpired();
```

#### **Get ready timestamp** (`getAfter()`)

Read the absolute ready timestamp (seconds since epoch).

```php
$after = $message->getAfter();
```

#### **Retry decisions** (`shouldRetry()`)

Check whether a message should be retried. This method increments the retry attempt counter, so call it only once per failure.

```php
$shouldRetry = $message->shouldRetry();
```

#### **Check uniqueness** (`isUnique()`)

Check whether the message is marked as unique.

```php
$unique = $message->isUnique();
```

#### **Get a message hash** (`getHash()`)

Use `getHash()` to identify messages for uniqueness checks (class name, method, and JSON-encoded arguments with sorted keys).

```php
$hash = $message->isUnique() ? $message->getHash() : null;
```

#### **Get the queue name** (`getQueue()`)

Read the configured queue name.

```php
$queueName = $message->getQueue();
```

#### **Get message config** (`getConfig()`)

Read the full message option array.

```php
$config = $message->getConfig();
```

### `RedisQueue`

`RedisQueue` implements the standard queue handler contract (see [`Queue`](#queue-1)) and adds Redis-specific behavior for delayed jobs and uniqueness.

#### **Enqueue** (`push()`)

Enqueue a message into Redis.

If the message is not ready yet, it is stored as delayed until its `after` timestamp.

Arguments:
- `$message` (`Message`): message to enqueue.

```php
$ok = $queue->push($message);
```

#### **Dequeue** (`pop()`)

Pop the next available message from Redis.

When delayed messages become ready, they are moved into the main queue before popping.

Arguments:
- `$queue` (`string`): queue name (defaults to `Queue::DEFAULT`).

```php
$message = $queue->pop('search');
```

#### **Enforce uniqueness** (`push()`)

When a message is marked unique, `RedisQueue` tracks a hash key to prevent enqueuing duplicates.

```php
$message = new Message([
    'className' => GenerateReportJob::class,
    'arguments' => ['reportId' => 123],
    'unique' => true,
]);

$ok = $queue->push($message);
```

#### **Get queue stats** (`stats()`)

Use the standard queue handler methods to inspect Redis queues.

```php
$defaultStats = $queue->stats();
```

#### **List queues** (`queues()`)

List the active queue names known to Redis.

```php
$queueNames = $queue->queues();
```

#### **Clear a queue** (`clear()`)

Remove all items from a queue.

```php
$queue->clear('default');
```

#### **Reset queue statistics** (`reset()`)

Reset a queue’s statistics.

```php
$queue->reset('default');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `delay` and `expires` are normalized into absolute `after` and `before` timestamps when the `Message` is constructed; creating messages long before enqueueing can shift timing unexpectedly.
- Invalid messages (`Message::isValid() === false`) are skipped and emit `Queue.invalid`; expired messages are dropped silently (no events).
- A job is considered failed when it returns `false` or throws; the handler decides whether to retry by implementing `Queue::fail()`.
- `Message::shouldRetry()` increments an internal attempt counter; calling it more than once per failure can consume retries unintentionally.
- `QueueManager::push()` does not surface the boolean return value from `Queue::push()`; handlers may decline to enqueue (for example, due to expiry or uniqueness) without raising an error.
- `RedisQueue` uniqueness is based on `Message::getHash()` (class name, method, and JSON-encoded arguments with sorted keys).
- `RedisQueue` retries have no built-in backoff: on failure, `RedisQueue::fail()` re-enqueues immediately when retries remain (unless the message already has a future `after` timestamp).
- `RedisQueue` uniqueness only applies while a message is waiting in Redis (queued or delayed). The uniqueness key is removed when the message is popped, so duplicates can be enqueued again while the job is executing.
- Design jobs to be idempotent (safe to run more than once) and side-effect aware, especially when enabling retries.
- `Worker::run()` is not re-entrant and uses `pcntl_async_signals()` with handlers for `SIGTERM` and `SIGQUIT`.
- Delays/expiries depend on system time. Keep worker hosts time-synced (for example via NTP) to avoid “early” or “late” execution when using `after`/`before`.

## Related

- [Config](../core/config.md)
- [Queue Worker](worker.md)
- [Built-in Console Commands](../console/commands.md)
- [Events](../events/index.md)
- [Container](../core/container.md)
