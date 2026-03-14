# Queue

Use queue handlers when you want to move slow or retryable work out of the main request or command flow.

Queue lets you push jobs, delay them, expire them, enforce uniqueness, and process them with long-running workers.

## Table of Contents

- [Start here](#start-here)
- [Queue overview](#queue-overview)
- [Configuring queue handlers](#configuring-queue-handlers)
  - [Base handler options](#base-handler-options)
  - [Example configuration](#example-configuration)
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
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual queue workflow is:

1. Configure a handler under the `Queue` config key.
2. Write a job class with a `run()` method.
3. Push jobs with `QueueManager::push()` or `queue()`.
4. Run a worker to process jobs.
5. Inspect queue health with `stats()` or `queue:stats`.

```php
use Fyre\Queue\QueueManager;

class SendWelcomeEmailJob
{
    public function run(Mailer $mailer, string $email): void
    {
        $mailer->sendWelcome($email);
    }
}

$queues = app(QueueManager::class);

$queues->push(SendWelcomeEmailJob::class, [
    'email' => 'user@example.com',
]);
```

The `queue($className, $arguments, $options)` helper pushes a job through the shared `QueueManager`; see [Helpers](../core/helpers.md).

## Queue overview

Most applications use the queue layer in four ways:

- configure one or more named handlers through [Config](../core/config.md)
- separate workloads into logical queue names such as `emails` or `search`
- push plain PHP job classes with arguments and delivery options
- run workers that consume and execute those jobs

Two queue settings are easy to confuse:

- `config` selects which configured handler to use
- `queue` selects which logical queue name inside that handler to use

Use `config` when you need different backends or connections. Use `queue` when you want to separate workloads within the same backend.

## Configuring queue handlers

Queue handler configuration is read from the `Queue` key in your app config. Each named entry is an options array used to build a handler.

### Base handler options

These options apply to all handlers:

- `className` (`class-string`) - handler class name, which must extend `Queue`

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

Use `QueueManager::use()` to get a shared handler for one of those config keys:

```php
$queue = $queues->use('default');
$stats = $queue->stats();
```

If you need a one-off handler without storing it under a config key, use `QueueManager::build()`; see the [Method guide](#method-guide).

## Built-in queue handlers

The framework ships built-in handlers under `Fyre\Queue\Handlers\*`.

### Redis

`RedisQueue` stores queued messages in Redis and is a good fit when you need delayed jobs, retries, and simple queue inspection.

It requires the `redis` PHP extension.

Options:

- `host` (`string`) - Redis host (default: `127.0.0.1`)
- `port` (`int`) - Redis port (default: `6379`)
- `password` (`string|null`) - Redis password (default: `null`)
- `database` (`int|null`) - Redis database index (default: `null`)
- `timeout` (`int`) - connection timeout in seconds (default: `0`)
- `persist` (`bool`) - whether to use a persistent connection (default: `true`)
- `tls` (`bool`) - whether to connect using `tls://` (default: `false`)
- `ssl` (`array`) - TLS client settings (`key`, `cert`, `ca`)

`RedisQueue` stores `Message` objects using PHP serialization, so treat the Redis instance as trusted infrastructure and lock it down accordingly.

## Writing jobs

Jobs are plain classes with a method that the worker can call. The default method name is `run`.

Prefer named arguments when you push jobs so the call stays readable and stable if you reorder parameters later. You can also type-hint services, and the container will resolve them when the job runs.

```php
class GenerateReportJob
{
    public function run(ReportService $reports, int $reportId): void
    {
        $reports->generate($reportId);
    }
}

$queues->push(GenerateReportJob::class, [
    'reportId' => $reportId,
]);
```

A job is treated as failed when it returns `false` or throws an exception. Any other return value is treated as success.

## Pushing jobs

Use `QueueManager::push()` to enqueue a job class and method call.

```php
$queues->push(SearchIndexJob::class, [
    'postId' => $postId,
], [
    'config' => 'default',
    'queue' => 'search',
    'delay' => 60,
    'unique' => true,
]);
```

To call a method other than `run`, set the `method` option:

```php
$queues->push(CacheWarmupJob::class, [
    'userId' => $userId,
], [
    'method' => 'handle',
]);
```

### Message options

The most common message options are:

- `method` (`string`) - job method name (default: `run`)
- `config` (`string`) - queue handler config key (default: `default`)
- `queue` (`string`) - logical queue name inside the handler (default: `default`)
- `delay` (`int`) - delay in seconds before the job becomes ready
- `expires` (`int`) - number of seconds before the job expires
- `retry` (`bool`) - whether retries are allowed (default: `true`)
- `maxRetries` (`int`) - maximum retry attempts (default: `5`)
- `unique` (`bool`) - whether the handler should enforce uniqueness (default: `false`)

For more direct control, you can also set absolute `after` and `before` timestamps instead of `delay` and `expires`.

## Processing jobs

Jobs are processed by a [Queue Worker](worker.md). The worker pops the next available message, executes the job, and marks it as complete or failed.

The built-in way to run a worker is the `queue:worker` console command; see [Console Commands](../console/commands.md#queueworker).

Queues are designed for at-least-once processing. A job may run more than once, so prefer idempotent job design.

## Inspecting queues

Use `Queue::stats()` to inspect the current queue state and `Queue::queues()` to list known queue names.

```php
$queue = $queues->use();

$queueNames = $queue->queues();
$stats = $queue->stats('search');
```

With `RedisQueue`, stats include `queued`, `delayed`, `completed`, `failed`, and `total`.

You can also inspect queue stats from the CLI using `queue:stats`; see [Console Commands](../console/commands.md#queuestats).

```php
$commandRunner->handle(['app', 'queue:stats']);
$commandRunner->handle(['app', 'queue:stats', '--config', 'default', '--queue', 'search']);
```

## Lifecycle events

The worker dispatches queue lifecycle events through the event system:

- `Queue.start` - `message`
- `Queue.success` - `message`
- `Queue.failure` - `message`, `shouldRetry`
- `Queue.exception` - `message`, `exception`, `shouldRetry`
- `Queue.invalid` - `message`

For event listening patterns, see [Events](../events/index.md).

## Method guide

Examples below assume `$queues` is a `QueueManager` instance, `$queue` is a `Queue` instance, and `$message` is a `Message` instance.

### `QueueManager`

#### **Push a job** (`push()`)

Queue a job as a class and method call.

Arguments:
- `$className` (`class-string`): job class name.
- `$arguments` (`array<string, mixed>`): arguments passed to the job method.
- `$options` (`array<string, mixed>`): message options such as `queue`, `delay`, `expires`, and `unique`.

```php
$queues->push(GenerateReportJob::class, [
    'reportId' => 123,
], [
    'queue' => 'reports',
    'delay' => 10,
]);
```

#### **Use a configured handler** (`use()`)

Get a shared handler instance for a config key.

Arguments:
- `$key` (`string`): handler config key (default: `default`).

```php
$stats = $queues->use()->stats();
```

#### **Build a one-off handler** (`build()`)

Build a handler directly from an options array without storing it under a config key.

Arguments:
- `$options` (`array<string, mixed>`): handler options, including `className`.

```php
use Fyre\Queue\Handlers\RedisQueue;

$tempQueue = $queues->build([
    'className' => RedisQueue::class,
    'host' => '127.0.0.1',
    'port' => 6379,
]);

$tempQueue->reset();
```

#### **Read handler config** (`getConfig()`)

Get all queue configs or one config by key.

Arguments:
- `$key` (`string|null`): config key, or `null` for all configs.

```php
$allConfigs = $queues->getConfig();
$defaultConfig = $queues->getConfig('default');
```

#### **Set handler config** (`setConfig()`)

Register a new queue config at runtime.

The key must not already exist.

Arguments:
- `$key` (`string`): config key.
- `$options` (`array<string, mixed>`): handler options.

```php
use Fyre\Queue\Handlers\RedisQueue;

$queues->setConfig('reports', [
    'className' => RedisQueue::class,
    'host' => '127.0.0.1',
    'port' => 6379,
    'database' => 2,
]);
```

### `Queue`

#### **List queue names** (`queues()`)

Return the set of queue names known to the handler.

```php
$queueNames = $queue->queues();
```

#### **Read queue stats** (`stats()`)

Return queue statistics for a queue name.

Arguments:
- `$queue` (`string`): queue name (default: `default`).

```php
$stats = $queue->stats('search');
```

#### **Clear a queue** (`clear()`)

Remove all pending items from a queue.

Arguments:
- `$queue` (`string`): queue name (default: `default`).

```php
$queue->clear('search');
```

#### **Reset queue statistics** (`reset()`)

Reset the stored counters for a queue.

Arguments:
- `$queue` (`string`): queue name (default: `default`).

```php
$queue->reset('search');
```

### `Message`

#### **Validate a message** (`isValid()`)

Check whether the target class and method exist.

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

#### **Retry decisions** (`shouldRetry()`)

Check whether a message should be retried.

```php
$shouldRetry = $message->shouldRetry();
```

#### **Get a uniqueness hash** (`getHash()`)

Get a stable hash based on the class name, method, and arguments.

```php
$hash = $message->getHash();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `delay` and `expires` are converted into absolute `after` and `before` timestamps when the `Message` is created.
- Invalid messages are skipped and emit `Queue.invalid`; expired messages are dropped silently.
- `QueueManager::push()` does not return whether the handler accepted the message, so uniqueness or expiry can prevent enqueueing without raising an error.
- `Message::shouldRetry()` increments the retry counter, so call it only once for a given failure.
- `RedisQueue` removes its uniqueness key when a message is popped, so uniqueness only applies while the job is waiting in Redis.
- `RedisQueue` retries happen immediately unless the message already has a future `after` timestamp.
- Delays and expiries depend on system time, so keep worker hosts time-synced.

## Related

- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
- [Queue Worker](worker.md)
- [Console Commands](../console/commands.md)
- [Events](../events/index.md)
