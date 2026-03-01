# Timers

`Timer` is a small utility for measuring elapsed time for named phases in a test or script. It uses `hrtime(true)` (a monotonic clock) and returns durations in seconds.

## Table of Contents

- [Purpose](#purpose)
- [Basic usage](#basic-usage)
- [Reading elapsed time](#reading-elapsed-time)
- [Inspecting and managing timers](#inspecting-and-managing-timers)
- [Method guide](#method-guide)
  - [Timing](#timing)
  - [Inspection](#inspection)
  - [Management](#management)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Timer` when you want a lightweight way to measure specific phases (bootstrap time, a query phase, serialization) without introducing a full benchmark runner.

If you want to run named callbacks repeatedly and collect time + memory results, see [Benchmark](benchmark.md).

## Basic usage

Timers are created by name:

```php
use Fyre\TestSuite\Timer;

$timer = new Timer();

$timer->start('bootstrap');
// ... work ...
$timer->stop('bootstrap');
```

You can stop all running timers at once:

```php
$timer->start('a');
$timer->start('b');

// ... work ...

$timer->stopAll();
```

## Reading elapsed time

`elapsed($name)` returns the duration in seconds:

- If the timer has been stopped, it returns the recorded duration.
- If the timer is still running, it returns the time since it was started.

```php
$timer = new Timer();
$timer->start('phase');

// ... work ...

$secondsSoFar = $timer->elapsed('phase');
$timer->stop('phase');
$finalSeconds = $timer->elapsed('phase');
```

## Inspecting and managing timers

Timer state is stored as timer data with:

- `start` (int nanoseconds)
- `end` (int nanoseconds or `null`)
- `duration` (float seconds or `null`)

Useful helpers:

- `all()` returns all timers.
- `get($name)` returns timer data or `null`.
- `has($name)` checks existence.
- `isStopped($name)` checks whether a timer is stopped.
- `remove($name)` removes a timer (throws if missing).
- `clear()` removes all timers.

## Method guide

### Timing

#### **Start a timer** (`start()`)

A timer name must be unique. Starting a timer records its start time.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->start('bootstrap');
```

#### **Stop a timer** (`stop()`)

Stopping a timer records its end time and freezes its duration.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->start('bootstrap');

// ... work ...

$timer->stop('bootstrap');
```

#### **Stop all running timers** (`stopAll()`)

Stops every timer that is currently running.

```php
$timer
    ->start('a')
    ->start('b');

// ... work ...

$timer->stopAll();
```

#### **Read elapsed time** (`elapsed()`)

Returns elapsed seconds for a timer. If the timer has already been stopped, this returns the recorded duration.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->start('phase');

// ... work ...

$seconds = $timer->elapsed('phase');
```

### Inspection

#### **Check whether a timer exists** (`has()`)

Returns `true` if a timer with the given name exists.

Arguments:
- `$name` (`string`): the timer name.

```php
if ($timer->has('phase')) {
    // ...
}
```

#### **Fetch timer data** (`get()`)

Returns the timer data array, or `null` if the timer does not exist.

Arguments:
- `$name` (`string`): the timer name.

```php
$data = $timer->get('phase');
```

#### **Check whether a timer is stopped** (`isStopped()`)

Returns whether the timer has been stopped.

Arguments:
- `$name` (`string`): the timer name.

```php
$stopped = $timer->isStopped('phase');
```

#### **Get all timers** (`all()`)

Returns all timers keyed by name.

```php
$timer
    ->start('a')
    ->start('b');

$all = $timer->all();
```

#### **Count timers** (`count()`)

Returns the number of timers currently stored.

```php
$timer
    ->start('a')
    ->start('b');

$count = $timer->count();
```

### Management

#### **Remove a timer** (`remove()`)

Removes a timer by name.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->remove('phase');
```

#### **Clear all timers** (`clear()`)

Clears all timers.

```php
$timer
    ->start('a')
    ->start('b');

$timer->clear();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `start($name)` throws if the timer already exists.
- `stop($name)` throws if the timer does not exist or was already stopped.
- `elapsed($name)` and `isStopped($name)` throw if the timer does not exist.
- `remove($name)` throws if the timer does not exist.
- `stopAll()` stops only timers that are currently running.

## Related

- [Testing](index.md)
- [Benchmark](benchmark.md)
