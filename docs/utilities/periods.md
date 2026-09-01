# Periods

Use `Period` (`Fyre\Utility\DateTime\Period`) for a bounded date/time range at a fixed granularity. Use `PeriodCollection` (`Fyre\Utility\DateTime\PeriodCollection`) for set-style operations across several ranges.

For individual date/time values, see [Date/time](datetime.md).

## Table of Contents

- [Creating a period](#creating-a-period)
- [Boundaries, count, and iteration](#boundaries-count-and-iteration)
- [Comparing and combining periods](#comparing-and-combining-periods)
- [Period collections](#period-collections)
- [Mutation and materialization](#mutation-and-materialization)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Creating a period

```php
use Fyre\Utility\DateTime\Period;

$period = new Period(
    '2026-02-01',
    '2026-02-05',
    'day',
    'none'
);
```

The start and end may be `DateTime` instances or strings accepted by `DateTime`. Granularity is one of `year`, `month`, `day`, `hour`, `minute`, or `second`.

The fourth argument identifies boundaries to exclude:

| Value | Start | End |
| --- | --- | --- |
| `none` | included | included |
| `start` | excluded | included |
| `end` | included | excluded |
| `both` | excluded | excluded |

`Period::getBoundaries($includesStart, $includesEnd)` performs the inverse mapping and returns the matching exclusion keyword.

## Boundaries, count, and iteration

`start()` and `end()` return the original boundaries. `includedStart()` and `includedEnd()` return the effective boundaries after exclusions have advanced or reduced them by one unit. `includesStart()`, `includesEnd()`, and `granularity()` expose the remaining configuration.

A period is an `Iterator<int, DateTime>` and yields every included value at its granularity:

```php
foreach ($period as $date) {
    echo $date->toDateString().PHP_EOL;
}
```

| Method | Result |
| --- | --- |
| `count()` | number of values yielded, including both effective boundaries |
| `length()` | distance between the effective boundaries; a one-value period has length `0` |
| `current()`, `key()`, `next()`, `rewind()`, `valid()` | standard iterator cursor operations |

Construction throws if exclusions leave the effective end before the effective start, so every valid period yields at least one value.

## Comparing and combining periods

| Method | Result |
| --- | --- |
| `includes($date)` | whether a date lies within the effective boundaries |
| `contains($other)` | whether the complete effective range of another period is contained |
| `equals($other)` | whether effective boundaries and granularity match |
| `overlapsWith($other)` | whether the effective ranges share at least one value |
| `touches($other)` | whether one effective start equals the other effective end |
| `overlap($other)` | shared range, or `null` |
| `overlapAll(...$others)` | range shared with every argument, or `null` |
| `overlapAny(...$others)` | collection of each non-empty pairwise overlap |
| `gap($other)` | uncovered range between non-overlapping periods, or `null` |
| `subtract($other)` | zero, one, or two remaining ranges as a collection |
| `subtractAll(...$others)` | ranges remaining after every argument is removed |
| `diffSymmetric($other)` | ranges present in only one of the two periods |
| `renew()` | same-span period beginning at the original end, with the same granularity and exclusions |

Boundary comparison methods operate on the effective start or end:

| Boundary | Equal | Before | Before or equal | After | After or equal |
| --- | --- | --- | --- | --- | --- |
| start | `startEquals()` | `startsBefore()` | `startsBeforeOrEquals()` | `startsAfter()` | `startsAfterOrEquals()` |
| end | `endEquals()` | `endsBefore()` | `endsBeforeOrEquals()` | `endsAfter()` | `endsAfterOrEquals()` |

Most period-to-period operations require matching granularities and throw `LogicException` when they differ.

## Period collections

Construct a collection from zero or more periods:

```php
use Fyre\Utility\DateTime\PeriodCollection;

$periods = new PeriodCollection(
    new Period('2026-02-01', '2026-02-05'),
    new Period('2026-02-10', '2026-02-12')
);
```

| Method | Result |
| --- | --- |
| `add(...$periods)` | new collection with periods appended |
| `sort()` | new collection ordered by included start timestamp |
| `unique()` | new collection retaining the first of each equal period |
| `boundaries()` | minimal period spanning the collection, or `null` when empty |
| `gaps()` | uncovered ranges within `boundaries()` |
| `intersect($period)` | pairwise overlaps with one period |
| `overlapAll(...$collections)` | intersection across every collection |
| `subtract($collection)` | ranges left after removing another collection |
| `count()` | number of periods |
| `current()`, `key()`, `next()`, `rewind()`, `valid()` | standard iterator cursor operations |

Collections do not automatically sort, merge, or normalize their periods. Call `sort()` when order matters; `unique()` removes duplicates but does not merge adjacent or overlapping ranges.

## Mutation and materialization

`Period` operations return new periods or collections. Its only changing state is the cursor used by `Iterator`.

`PeriodCollection` methods also return new collections, but its `ArrayAccess` implementation mutates the original collection:

```php
$periods[] = new Period('2026-03-01', '2026-03-03');
$periods[0] = new Period('2026-01-01', '2026-01-02');
unset($periods[1]);
```

Assigned values must be `Period` instances. Reading a missing index throws `OutOfBoundsException`. Unsetting a non-final index leaves a gap, and iteration stops at the first missing integer index; avoid `unset()` when later periods must remain iterable.

These operations are implemented by `offsetExists()`, `offsetGet()`, `offsetSet()`, and `offsetUnset()`.

Operations materialize their results immediately. There is no lazy period pipeline: large ranges can be iterated without first creating an array of dates, but collection operations build their returned period lists in memory.

## Behavior notes

- Period comparisons use the date/time calendar operation matching the configured granularity.
- A shared instant is an overlap only when it belongs to both periods after exclusions are applied.
- `touches()` means the effective boundaries are equal; it does not mean two discrete periods are separated by exactly one unit.
- A collection does not validate that all periods use the same granularity. Operations that compare incompatible periods can therefore throw later.
- `boundaries()` uses the granularity and boundary inclusion of the periods supplying the earliest start and latest end.
- `overlapAll()` with no additional collections returns a clone; subtracting an empty collection also returns a clone.
- Both classes support instance macros; see [Macros](../core/macros.md).

## Related

- [Utilities](index.md)
- [Date/time](datetime.md)
