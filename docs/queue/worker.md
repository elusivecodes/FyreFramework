# Queue Worker

Use `Fyre\Queue\Worker` when you want a long-running process that consumes queued jobs.

Most applications start workers through the built-in `queue:worker` console command rather than constructing `Worker` directly.

## Table of Contents

- [Start here](#start-here)
- [Running the worker](#running-the-worker)
- [Job outcomes](#job-outcomes)
- [Lifecycle events](#lifecycle-events)
- [Worker options](#worker-options)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual worker flow is:

1. Configure a queue handler and start pushing jobs.
2. Run `queue:worker` for the handler config and queue name you want to process.
3. Keep workers under a process supervisor so they restart on failure or after rotation.
4. Use `maxJobs` or `maxRuntime` to rotate workers periodically.

For queue setup and enqueueing, see [Queue](index.md).

## Running the worker

The built-in way to start a worker is the `queue:worker` console command; see [Console Commands](../console/commands.md#queueworker).

The command accepts the main worker options:

- `config` - queue handler config key
- `queue` - queue name to poll
- `maxJobs` - maximum jobs before stopping
- `maxRuntime` - maximum runtime in seconds before stopping

Examples:

```php
$commandRunner->handle(['app', 'queue:worker']);
$commandRunner->handle(['app', 'queue:worker', '--queue', 'emails', '--max-runtime', '3600']);
```

`queue:worker` requires the `pcntl` extension and process forking support.

Recommended production setup:

- Run separate workers for different queue names when workloads have different priorities or runtimes.
- Set `maxJobs` or `maxRuntime` so a supervisor can rotate workers cleanly.
- Use more than one worker process for a queue when you need higher throughput.

If you need custom polling delays, build and run `Worker` directly instead of going through the command.

## Job outcomes

Each worker job runs through the container, so job arguments can be passed by name and services can be resolved by type hint.

The worker handles results like this:

- Invalid messages are skipped and emit `Queue.invalid`.
- Expired messages are dropped silently.
- Returning `false` marks the job as failed and emits `Queue.failure`.
- Throwing an exception marks the job as failed and emits `Queue.exception`.
- Any other return value marks the job as successful and emits `Queue.success`.

Before each valid job runs, the worker clears scoped container services so each job gets a fresh scoped state; see [Container](../core/container.md).

There is no built-in per-job timeout. If jobs call external systems, set timeouts in those clients and let your process supervisor restart stuck workers if needed.

## Lifecycle events

The worker dispatches these events through the event system:

- `Queue.start` - `message`
- `Queue.success` - `message`
- `Queue.failure` - `message`, `shouldRetry`
- `Queue.exception` - `message`, `exception`, `shouldRetry`
- `Queue.invalid` - `message`

`shouldRetry` is the boolean return value from `Queue::fail($message)`.

When you register listeners with `EventManager::on()`, the `Event` object is passed first, followed by the values listed above.

```php
use Fyre\Event\Event;
use Fyre\Queue\Message;
use Throwable;

$eventManager->on('Queue.failure', static function(Event $event, Message $message, bool $shouldRetry): void {
    log_message('debug', 'Queue failure (retry='.(int) $shouldRetry.'): '.$message->getHash());
});

$eventManager->on('Queue.exception', static function(Event $event, Message $message, Throwable $exception, bool $shouldRetry): void {
    log_message('error', 'Queue exception: '.$exception->getMessage());
});
```

For broader event-listener patterns, see [Events](../events/index.md).

## Worker options

Pass worker options as the fourth argument when you construct `Worker` directly:

- `config` (`string`) - queue handler config key (default: `default`)
- `queue` (`string`) - queue name to poll (default: `default`)
- `maxJobs` (`int`) - maximum number of jobs before stopping (default: `0`, unlimited)
- `maxRuntime` (`int`) - maximum runtime in seconds before stopping (default: `0`, unlimited)
- `rest` (`int`) - microseconds to sleep after processing a job (default: `10000`)
- `sleep` (`int`) - microseconds to sleep when no job is available (default: `1000000`)

```php
use Fyre\Queue\Worker;

$worker = new Worker($container, $queueManager, $eventManager, [
    'queue' => 'emails',
    'maxJobs' => 100,
    'maxRuntime' => 3600,
    'rest' => 10000,
    'sleep' => 1000000,
]);

$worker->run();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `Worker::run()` is not re-entrant, so calling it again on the same instance while it is already running does nothing.
- Stop signals are handled gracefully: the worker finishes the current job and then exits the loop.
- `maxJobs` counts only jobs that actually reach execution. Invalid and expired messages do not increment the counter.
- Retries mean a job may run more than once, so design queue jobs to be idempotent.

## Related

- [Queue](index.md)
- [Console Commands](../console/commands.md)
- [Events](../events/index.md)
- [Container](../core/container.md)
