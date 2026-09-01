# Benchmark

Use `Benchmark` for a quick in-process comparison of named callbacks. Each callback runs synchronously for the same number of iterations and reports total time and peak additional memory.

Benchmark results are measurements, not PHPUnit assertions. Use [Timers](timers.md) when named phases should run only once.

## Table of Contents

- [Run a benchmark](#run-a-benchmark)
- [Read results](#read-results)
- [Method guide](#method-guide)
  - [Registration](#registration)
  - [Execution](#execution)
  - [Inspection and management](#inspection-and-management)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Run a benchmark

Create one `Benchmark`, register the callbacks being compared, and run each for a fixed iteration count:

```php
use Fyre\TestSuite\Benchmark;

$benchmark = new Benchmark();

$benchmark
    ->add(
        'json',
        static fn(): string => (string) json_encode(['id' => 1])
    )
    ->add(
        'serialize',
        static fn(): string => serialize(['id' => 1])
    );

$results = $benchmark->run(5000);
```

Adding the same name again replaces its callback without changing the number of registered tests.

## Read results

`run()` returns one result per registered name in registration order:

```php
[
    'json' => [
        'time' => 0.0123,
        'memory' => 0,
        'n' => 5000,
    ],
    'serialize' => [
        'time' => 0.0158,
        'memory' => 0,
        'n' => 5000,
    ],
]
```

Each result always contains:

| Key | Type | Meaning |
| --- | --- | --- |
| `time` | `float` | total wall time in seconds for every iteration |
| `memory` | `int` | peak additional allocated memory in bytes |
| `n` | `int` | requested iteration count |

The sample timings are illustrative; runtime and memory values vary between runs. Calculate `time / n` for per-iteration comparisons, and repeat a benchmark before drawing conclusions from small differences.

## Method guide

The setup above establishes the shared `$benchmark` and its registered callbacks.

### Registration

#### **Add or replace a callback** (`add()`)

```php
add(string $name, callable $callback): static
```

Callbacks receive no arguments from the benchmark runner. Their return values are discarded after every iteration.

### Execution

#### **Run every registered callback** (`run()`)

```php
run(int $iterations = 1000): array
```

Each callback runs `$iterations` times. Values below `1` throw an `InvalidArgumentException` with `Iterations must be greater than 0.`

### Inspection and management

| Method | Return | Behavior |
| --- | --- | --- |
| `get($name)` | `callable|null` | return one callback or `null` when missing |
| `has($name)` | `bool` | check whether a name is registered |
| `all()` | `array<string, callable>` | return every callback keyed by name |
| `count()` | `int` | return the number of callbacks; also available through `count($benchmark)` |
| `remove($name)` | `static` | remove one callback and throw when it is missing |
| `clear()` | `void` | remove every callback |

## Behavior notes

- `run()` calls `gc_collect_cycles()` once before each named callback begins.
- Timing uses `hrtime(true)` around the complete iteration loop.
- Memory uses `memory_get_usage(true)` and records the peak increase from the start of that callback's run.
- Callback results are unset after each iteration and are not included in the returned data.
- In-process benchmarks are noisy and are not a substitute for representative profiling or load testing.

## Related

- [Testing](index.md)
- [Timers](timers.md)
