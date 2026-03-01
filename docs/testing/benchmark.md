# Benchmark

`Benchmark` provides simple in-process benchmarking for named tests. Each test callback is executed synchronously for a requested number of iterations, and results include total runtime and peak additional memory usage.

## Table of Contents

- [Purpose](#purpose)
- [Registering tests](#registering-tests)
- [Running benchmarks](#running-benchmarks)
- [Understanding results](#understanding-results)
- [Method guide](#method-guide)
  - [Methods](#methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Benchmark` when you want a lightweight way to compare multiple approaches in the same PHP process (for example: two parsing strategies, hydration approaches, or serialization routines).

It is not a substitute for full profiling, but it’s useful for quick “A vs B” checks during development.

If you want to measure named phases (start/stop) rather than repeatedly running callbacks, see [Timers](timers.md).

## Registering tests

Benchmarks are stored as named callables. Adding a test with an existing name replaces the previous callback.

```php
use Fyre\TestSuite\Benchmark;

$bench = new Benchmark();

$bench->add('json_encode', fn(): string => json_encode(['a' => 1, 'b' => 2]));
$bench->add('serialize', fn(): string => serialize(['a' => 1, 'b' => 2]));
```

## Running benchmarks

`run(int $iterations = 1000): array` executes each registered callback `$iterations` times and returns results keyed by test name.

```php
use Fyre\TestSuite\Benchmark;

$bench = (new Benchmark())
    ->add('a', fn(): int => 1 + 1)
    ->add('b', fn(): int => 2 + 2);

$results = $bench->run(5000);

// [
//   'a' => ['time' => 0.0123, 'memory' => 0, 'n' => 5000],
//   'b' => ['time' => 0.0109, 'memory' => 0, 'n' => 5000],
// ]
```

## Understanding results

Each result contains:

- `time` — total wall time in seconds (float) for all iterations.
- `memory` — peak additional memory usage in bytes (int) observed during the iterations.
- `n` — iteration count (int).

To compare tests, consider time per iteration (`time / n`) and relative differences between runs.

## Method guide

### Methods

#### **Add a test** (`add()`)

Register a callback under a name.

Arguments:
- `$name` (`string`): the test name.
- `$callback` (`callable`): the test callback.

```php
$bench->add('json_encode', fn(): string => json_encode(['a' => 1]));
$bench->add('serialize', fn(): string => serialize(['a' => 1]));
```

#### **Run benchmarks** (`run()`)

Execute each registered callback a fixed number of times and return the results indexed by test name.

Arguments:
- `$iterations` (`int`): the number of iterations per test.

```php
$bench->add('a', fn(): int => 1 + 1);
$bench->add('b', fn(): int => 2 + 2);

$results = $bench->run(5000);
$a = $results['a'];

$secondsPerIteration = $a['time'] / $a['n'];
```

#### **Remove a test** (`remove()`)

Remove a registered test by name. This method throws an `InvalidArgumentException` if the test does not exist.

Arguments:
- `$name` (`string`): the test name.

```php
$bench->add('a', fn(): int => 1 + 1);

if ($bench->has('a')) {
    $bench->remove('a');
}
```

#### **Get a test callback** (`get()`)

Fetch a registered callback by name.

Arguments:
- `$name` (`string`): the test name.

```php
$bench->add('a', fn(): int => 1 + 1);

$callback = $bench->get('a');

if ($callback) {
    $callback();
}
```

#### **Check whether a test exists** (`has()`)

Check whether a test name is registered.

Arguments:
- `$name` (`string`): the test name.

```php
$bench->add('a', fn(): int => 1 + 1);

if ($bench->has('a')) {
    $bench->run();
}
```

#### **List all tests** (`all()`)

Get all registered tests indexed by name.

```php
$bench->add('a', fn(): int => 1 + 1);
$bench->add('b', fn(): int => 2 + 2);

foreach ($bench->all() as $name => $test) {
    $test();
}
```

#### **Clear all tests** (`clear()`)

Remove every registered test.

```php
$bench->add('a', fn(): int => 1 + 1);

$bench->clear();
```

#### **Count registered tests** (`count()`)

Return the number of registered tests (this class implements `Countable`, so `count($bench)` works).

```php
$bench->add('a', fn(): int => 1 + 1);
$bench->add('b', fn(): int => 2 + 2);

$total = count($bench);
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Benchmarking is inherently noisy; compare relative differences and rerun multiple times.
- `run()` throws an `InvalidArgumentException` if `$iterations` is less than `1`.
- Timing uses `hrtime(true)` (a monotonic clock) and reports total wall time for the entire loop.
- Each test triggers `gc_collect_cycles()` once before timing begins, but allocations inside callbacks can still vary between iterations.
- Memory is sampled using `memory_get_usage(true)` and reported as the peak additional memory usage relative to the starting memory for that test.
- The callback result is assigned and then unset on every iteration, so return values are not retained across iterations by the benchmark runner.

## Related

- [Testing](index.md)
- [Timers](timers.md)
