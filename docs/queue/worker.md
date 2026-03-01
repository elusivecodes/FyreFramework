# Queue Worker

`Fyre\Queue\Worker` consumes messages from a queue handler and executes each job through the container. It’s designed to run as a long-lived process: it polls, sleeps when idle, and stops when it reaches configured limits or receives a stop signal.

## Table of Contents

- [Purpose](#purpose)
- [Running the worker](#running-the-worker)
- [Where the worker fits](#where-the-worker-fits)
- [Runtime loop](#runtime-loop)
- [Job execution](#job-execution)
- [Lifecycle events](#lifecycle-events)
- [Worker options](#worker-options)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 The worker is the “consume” side of the queue subsystem: it repeatedly pops messages from a handler, invokes the job method through the container, and emits lifecycle events so you can observe failures and retries.

For queue concepts and how messages are produced, see [Queue](index.md).

## Running the worker

📌 The most common way to start a worker is the built-in `queue:worker` console command (see [Built-in Console Commands](../console/commands.md#queueworker)).

This command forks:

- The parent process prints the PID and exits immediately.
- The child process builds a `Worker` and calls `run()`.

📌 Note: `queue:worker` requires the `pcntl` extension and `pcntl_fork()`. In production, run workers under a process supervisor so they restart on failure.

Options supported by `queue:worker` are forwarded to `Worker` as-is:

- `config` — queue handler config key
- `queue` — queue name to poll
- `maxJobs` — maximum jobs before stopping
- `maxRuntime` — maximum runtime in seconds before stopping

To customize polling delays (`rest`, `sleep`), build and run a `Worker` directly (see [Worker options](#worker-options)).

📌 Recommended production setup:

- Run one worker process per logical queue when workloads have different performance or priority characteristics.
- Set `maxRuntime` and/or `maxJobs` so a supervisor can rotate workers periodically (memory leaks, long-lived connections, and code deploys are easier to manage).

## Where the worker fits

- The queue handler instance is selected via a queue config key (resolved by `QueueManager`).
- The queue name (default `Queue::DEFAULT`) selects which logical queue inside that handler is polled.
- Running multiple worker processes against the same handler + queue increases throughput by processing messages in parallel.

## Runtime loop

`Worker::run()` is a polling loop:

1. Check stop limits (`maxJobs`, `maxRuntime`).
2. Pop the next message: `Queue::pop($queueName)`.
3. If a message is available, process it and then rest (`usleep($rest)`).
4. If no message is available, sleep longer (`usleep($sleep)`).

Both `rest` and `sleep` are microseconds (the unit used by `usleep()`).

## Job execution

When a message is popped, the worker applies a small set of rules before it executes anything:

- Invalid messages are skipped and emit `Queue.invalid`.
- Expired messages are dropped silently (no events).
- Before executing a valid, non-expired job, the worker calls `Container::clearScoped()` to reset scoped instances for the next job. See [Container](../core/container.md).

Execution uses the container to invoke the job method with the message arguments. Outcomes are treated as:

- Return value `false`: `Queue::fail()` is called and `Queue.failure` is emitted.
- Any other return value: `Queue::complete()` is called and `Queue.success` is emitted.
- Thrown exception: `Queue::fail()` is called and `Queue.exception` is emitted.

📌 Note: There is no built-in per-job timeout. If jobs can block (network calls, slow queries), enforce timeouts in the job itself (HTTP client timeouts, DB statement timeouts) and rely on a supervisor to restart stuck workers.

## Lifecycle events

The worker dispatches these lifecycle events through `EventManager`:

- `Queue.start` — `message`
- `Queue.success` — `message`
- `Queue.failure` — `message`, `shouldRetry`
- `Queue.exception` — `message`, `exception`, `shouldRetry`
- `Queue.invalid` — `message`

`shouldRetry` is the boolean return value from `Queue::fail($message)`.

When using `EventManager::on()`, listeners receive the `Event` instance as the first argument, followed by the event data values in the order listed above.

Register listeners on the same `EventManager` instance that is passed to the `Worker`. For a broader overview of event concepts and listener patterns, see [Events](../events/index.md).

```php
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Fyre\Queue\Message;
use Throwable;

$eventManager->on('Queue.start', function(Event $event, Message $message): void {
    error_log('Queue start: '.$message->getHash());
});

$eventManager->on('Queue.failure', function(Event $event, Message $message, bool $shouldRetry): void {
    error_log('Queue failure (retry='.(int) $shouldRetry.'): '.$message->getHash());
});

$eventManager->on('Queue.exception', function(Event $event, Message $message, Throwable $exception, bool $shouldRetry): void {
    error_log('Queue exception (retry='.(int) $shouldRetry.'): '.$exception->getMessage());
});
```

## Worker options

Options are supplied as the fourth argument to the `Worker` constructor:

- `config` (`string`) — queue handler config key used by `QueueManager` (default: `QueueManager::DEFAULT`)
- `queue` (`string`) — queue name passed to `Queue::pop()` (default: `Queue::DEFAULT`)
- `maxJobs` (`int`) — maximum number of processed jobs before stopping (default: `0`, unlimited)
- `maxRuntime` (`int`) — maximum runtime in seconds before stopping (default: `0`, unlimited)
- `rest` (`int`) — microseconds to sleep after processing a message (default: `10000`)
- `sleep` (`int`) — microseconds to sleep when no message is available (default: `1000000`)

```php
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;

$worker = new Worker($container, $queueManager, $eventManager, [
    'config' => QueueManager::DEFAULT,
    'queue' => Queue::DEFAULT,
    'maxJobs' => 100,
    'maxRuntime' => 3600,
    'rest' => 10000,
    'sleep' => 1000000,
]);

$worker->run();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `Worker::run()` is not re-entrant; a second call on the same instance returns immediately.
- Signal handling uses `pcntl_async_signals()` and installs handlers for `SIGTERM` and `SIGQUIT` (the worker requires the `pcntl` extension).
- Stop behavior is graceful: when a stop signal is received, the worker finishes the current message (if any), then exits the loop.
- `maxJobs` counts only jobs that reach execution (success, failure, or exception). Invalid and expired messages do not increment the job counter.
- Expired messages are dropped silently (no events).
- Retries mean a job may run more than once. Prefer idempotent job design (for example, check a database flag, use upserts, or use external locks) so reprocessing is safe.

## Related

- [Queue](index.md)
- [Built-in Console Commands](../console/commands.md)
- [Events](../events/index.md)
- [Container](../core/container.md)
