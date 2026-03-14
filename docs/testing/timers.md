# Timers

Use `Timer` when you want lightweight timing around named phases in a test or script.

It uses `hrtime(true)` and returns durations in seconds.

## Table of Contents

- [Start here](#start-here)
- [Reading elapsed time](#reading-elapsed-time)
- [Inspecting timers](#inspecting-timers)
- [Method guide](#method-guide)
  - [Timing](#timing)
  - [Inspection](#inspection)
  - [Management](#management)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use named timers when you want to measure a few phases such as bootstrap, rendering, or serialization without setting up a full benchmark run.

If you want repeated callback execution with time and memory results, see [Benchmark](benchmark.md).

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

## Inspecting timers

Timer state is stored as timer data with:

- `start` (int nanoseconds)
- `end` (int nanoseconds or `null`)
- `duration` (float seconds or `null`)

Useful helpers:

- `all()` returns all timers
- `get($name)` returns timer data or `null`
- `has($name)` checks whether a timer exists
- `isStopped($name)` checks whether a timer has been stopped
- `remove($name)` removes a timer
- `clear()` removes all timers

## Method guide

Examples below assume you already have a `$timer` instance.

### Timing

#### **Start a timer** (`start()`)

Start a named timer.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->start('bootstrap');
```

#### **Stop a timer** (`stop()`)

Stop a timer and freeze its duration.

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

Return elapsed seconds for a timer.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->start('phase');
// ... work ...
$seconds = $timer->elapsed('phase');
```

### Inspection

#### **Check whether a timer exists** (`has()`)

Check whether a timer exists.

Arguments:
- `$name` (`string`): the timer name.

```php
if ($timer->has('phase')) {
    // ...
}
```

#### **Fetch timer data** (`get()`)

Return the timer data array, or `null` if the timer does not exist.

Arguments:
- `$name` (`string`): the timer name.

```php
$data = $timer->get('phase');
```

#### **Check whether a timer is stopped** (`isStopped()`)

Return whether the timer has been stopped.

Arguments:
- `$name` (`string`): the timer name.

```php
$stopped = $timer->isStopped('phase');
```

#### **Get all timers** (`all()`)

Return all timers keyed by name.

```php
$timer
    ->start('a')
    ->start('b');

$all = $timer->all();
```

#### **Count timers** (`count()`)

Return the number of timers currently stored.

```php
$timer
    ->start('a')
    ->start('b');

$count = $timer->count();
```

### Management

#### **Remove a timer** (`remove()`)

Remove a timer by name.

Arguments:
- `$name` (`string`): the timer name.

```php
$timer->remove('phase');
```

#### **Clear all timers** (`clear()`)

Clear all timers.

```php
$timer
    ->start('a')
    ->start('b');

$timer->clear();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `start($name)` throws if the timer already exists.
- `stop($name)` throws if the timer does not exist or was already stopped.
- `elapsed($name)` and `isStopped($name)` throw if the timer does not exist.
- `remove($name)` throws if the timer does not exist.
- `stopAll()` stops only timers that are currently running.

## Related

- [Testing](index.md)
- [Benchmark](benchmark.md)
