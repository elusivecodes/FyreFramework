# Timers

Use `Timer` to measure named phases that run once. Timers use the monotonic `hrtime(true)` clock and report elapsed seconds.

Use [Benchmark](benchmark.md) instead when the same callback should run repeatedly with time and memory results.

## Table of Contents

- [Measure named phases](#measure-named-phases)
- [Read timer data](#read-timer-data)
- [Method guide](#method-guide)
  - [Timing](#timing)
  - [Inspection](#inspection)
  - [Management](#management)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Measure named phases

Create one timer and use distinct names for the phases being measured:

```php
use Fyre\TestSuite\Timer;

$timer = new Timer();

$timer->start('bootstrap');
// Bootstrap the application.
$timer->stop('bootstrap');

$timer->start('render');
// Render the response.
$timer->stop('render');

$bootstrapSeconds = $timer->elapsed('bootstrap');
$renderSeconds = $timer->elapsed('render');
```

`elapsed()` returns the frozen duration after `stop()`. While a timer is running, it returns the time from `start()` to the current monotonic clock reading.

Call `stopAll()` when several running timers should share the same end timestamp.

## Read timer data

`get($name)` returns `null` for an unknown name. A known timer has this exact shape:

```php
[
    'start' => 123456789000000,
    'end' => 123456790000000,
    'duration' => 0.001,
]
```

`start` and `end` are monotonic nanosecond readings. `end` and `duration` remain `null` until the timer is stopped. The numeric values above illustrate the relationship; actual clock readings vary.

`all()` returns the same arrays keyed by timer name.

## Method guide

The examples above establish the shared `$timer` instance used by these methods.

### Timing

| Method | Return | Behavior |
| --- | --- | --- |
| `start($name)` | `static` | create and start a named timer |
| `stop($name)` | `static` | stop one running timer and store its duration |
| `stopAll()` | `static` | stop every running timer using one end reading |
| `elapsed($name)` | `float` | return running or final elapsed seconds |

### Inspection

| Method | Return | Behavior |
| --- | --- | --- |
| `has($name)` | `bool` | check whether a timer exists |
| `get($name)` | `array|null` | return timer data without changing it |
| `isStopped($name)` | `bool` | check whether the timer has an end reading |
| `all()` | `array<string, array>` | return all timer data keyed by name |
| `count()` | `int` | return the number of stored timers; also available through `count($timer)` |

### Management

| Method | Return | Behavior |
| --- | --- | --- |
| `remove($name)` | `static` | remove one existing timer |
| `clear()` | `void` | remove every timer |

## Behavior notes

- `start()` throws ``Timer `bootstrap` has already been started.`` for an existing `bootstrap` timer, even if it has stopped.
- `stop()` throws when the timer does not exist or has already stopped.
- `elapsed()`, `isStopped()`, and `remove()` throw when the timer does not exist.
- `get()` is the non-throwing lookup and returns `null` for an unknown timer.
- `stopAll()` leaves timers that have already stopped unchanged.

## Related

- [Testing](index.md)
- [Benchmark](benchmark.md)
