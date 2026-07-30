# Benchmark

Use `Benchmark` when you want a quick in-process comparison between a few named callbacks.

Each registered callback runs synchronously for a chosen number of iterations, and the results include total runtime and peak additional memory usage.

## Table of Contents

- [Start here](#start-here)
- [Registering tests](#registering-tests)
- [Running benchmarks](#running-benchmarks)
- [Reading results](#reading-results)
- [Method guide](#method-guide)
  - [Methods](#methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Create a `Benchmark`.
2. Register a few named callbacks with `add()`.
3. Run them with a fixed iteration count.
4. Compare total time, time per iteration, and memory.

It is a good fit for quick "A vs B" checks during development. If you want to measure named phases instead of repeatedly running callbacks, see [Timers](timers.md).

## Registering tests

Benchmarks are stored as named callables. Adding a test with an existing name replaces the previous callback.

```php
use Fyre\TestSuite\Benchmark;

$bench = new Benchmark();

$bench->add('json_encode', static fn(): string => json_encode(['a' => 1, 'b' => 2]));
$bench->add('serialize', static fn(): string => serialize(['a' => 1, 'b' => 2]));
```

## Running benchmarks

`run(int $iterations = 1000): array` executes each registered callback `$iterations` times and returns results keyed by test name.

```php
$bench = new Benchmark()
    ->add('a', static fn(): int => 1 + 1)
    ->add('b', static fn(): int => 2 + 2);

$results = $bench->run(5000);

// [
//   'a' => ['time' => 0.0123, 'memory' => 0, 'n' => 5000],
//   'b' => ['time' => 0.0109, 'memory' => 0, 'n' => 5000],
// ]
```

## Reading results

Each result contains:

- `time` - total wall time in seconds for all iterations
- `memory` - peak additional memory usage in bytes
- `n` - iteration count

To compare tests, consider time per iteration (`time / n`) and relative differences between runs.

## Method guide

Examples below assume you already have a `$bench` instance.

### Methods

#### **Add a test** (`add()`)

Register a callback under a name.

Arguments:
- `$name` (`string`): the test name.
- `$callback` (`callable`): the test callback.

```php
$bench->add('json_encode', static fn(): string => json_encode(['a' => 1]));
$bench->add('serialize', static fn(): string => serialize(['a' => 1]));
```

#### **Run benchmarks** (`run()`)

Execute each registered callback a fixed number of times and return the results indexed by test name.

Arguments:
- `$iterations` (`int`): the number of iterations per test.

```php
$bench->add('a', static fn(): int => 1 + 1);
$bench->add('b', static fn(): int => 2 + 2);

$results = $bench->run(5000);
$a = $results['a'];

$secondsPerIteration = $a['time'] / $a['n'];
```

#### **Remove a test** (`remove()`)

Remove a registered test by name.

Arguments:
- `$name` (`string`): the test name.

```php
$bench->add('a', static fn(): int => 1 + 1);

if ($bench->has('a')) {
    $bench->remove('a');
}
```

#### **Get a test callback** (`get()`)

Fetch a registered callback by name.

Arguments:
- `$name` (`string`): the test name.

```php
$bench->add('a', static fn(): int => 1 + 1);

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
$bench->add('a', static fn(): int => 1 + 1);

if ($bench->has('a')) {
    $bench->run();
}
```

#### **List all tests** (`all()`)

Get all registered tests indexed by name.

```php
$bench->add('a', static fn(): int => 1 + 1);
$bench->add('b', static fn(): int => 2 + 2);

foreach ($bench->all() as $name => $test) {
    $test();
}
```

#### **Clear all tests** (`clear()`)

Remove every registered test.

```php
$bench->add('a', static fn(): int => 1 + 1);

$bench->clear();
```

#### **Count registered tests** (`count()`)

Return the number of registered tests.

```php
$bench->add('a', static fn(): int => 1 + 1);
$bench->add('b', static fn(): int => 2 + 2);

$total = count($bench);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Benchmarking is inherently noisy, so compare relative differences and rerun multiple times.
- `run()` throws an `InvalidArgumentException` if `$iterations` is less than `1`.
- Timing uses `hrtime(true)` and reports total wall time for the full loop.
- Each test triggers `gc_collect_cycles()` once before timing begins, but allocations inside callbacks can still vary between iterations.
- Memory is sampled using `memory_get_usage(true)` and reported as the peak additional memory usage relative to the starting memory for that test.
- The callback result is unset on every iteration, so results are not retained by the benchmark runner.

## Related

- [Testing](index.md)
- [Timers](timers.md)
