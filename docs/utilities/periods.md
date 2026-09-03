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
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;

$days = new Period(
    Date::createFromArray([2026, 2, 1]),
    Date::createFromArray([2026, 2, 5])
);

$hours = new Period(
    DateTime::createFromArray([2026, 2, 1, 9]),
    DateTime::createFromArray([2026, 2, 1, 17]),
    'hour'
);
```

The start and end must both be `Date` instances or both be `DateTime` instances. String and `Time` boundaries are not accepted.

`Date` periods support `year`, `month`, and `day` granularities. `DateTime` periods additionally support `hour`, `minute`, and `second`.

The fourth argument identifies boundaries to exclude:

| Value | Start | End |
| --- | --- | --- |
| `none` | included | included |
| `start` | excluded | included |
| `end` | included | excluded |
| `both` | excluded | excluded |

`Period::getBoundaries($includesStart, $includesEnd)` performs the inverse mapping and returns the matching exclusion keyword.

## Boundaries, count, and iteration

`start()` and `end()` return the original boundaries. `includedStart()` and `includedEnd()` return the effective boundaries after exclusions have advanced or reduced them by one unit. These methods preserve the concrete boundary type. `includesStart()`, `includesEnd()`, and `granularity()` expose the remaining configuration.

A period yields every included value at its granularity, using the same `Date` or `DateTime` type as its boundaries:

```php
foreach ($days as $date) {
    echo $date->toIsoString().PHP_EOL;
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
| `equals($other)` | whether the effective boundaries match |
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

Dates passed to boundary comparison methods must use the same concrete type as the period. Operations that compare periods require both periods to use the same concrete date type and granularity. A date-type mismatch throws `InvalidArgumentException`; a granularity mismatch throws `LogicException`.

Use `Period::checkCompatibility($a, $b)` to validate these requirements directly.

## Period collections

Construct a collection from zero or more periods:

```php
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

$periods = new PeriodCollection(
    new Period(
        Date::createFromArray([2026, 2, 1]),
        Date::createFromArray([2026, 2, 5])
    ),
    new Period(
        Date::createFromArray([2026, 2, 10]),
        Date::createFromArray([2026, 2, 12])
    )
);
```

Every period in a collection must use the same concrete date type and granularity. This is validated when the collection is constructed and whenever periods are added.

| Method | Result |
| --- | --- |
| `add(...$periods)` | new collection with periods appended |
| `get($index)` | period at an index; throws `OutOfBoundsException` when missing |
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

Operations that produce periods or collections return new instances. Calling `add()`, for example, does not alter the original collection:

```php
echo $periods->get(0)->start()->toIsoString(); // 2026-02-01

$updated = $periods->add(
    new Period(
        Date::createFromArray([2026, 3, 1]),
        Date::createFromArray([2026, 3, 3])
    )
);
```

`PeriodCollection` does not implement `ArrayAccess`; use `get()` to read a period by index. The iterator cursor is the only changing state in either class.

Operations materialize their results immediately. There is no lazy period pipeline: large ranges can be iterated without first creating an array of dates, but collection operations build their returned period lists in memory.

## Behavior notes

- Period comparisons use the date/time calendar operation matching the configured granularity.
- New periods and collections produced by operations retain the concrete boundary type.
- A shared instant is an overlap only when it belongs to both periods after exclusions are applied.
- `touches()` means the effective boundaries are equal; it does not mean two discrete periods are separated by exactly one unit.
- `boundaries()` uses the granularity and boundary inclusion of the periods supplying the earliest start and latest end.
- `overlapAll()` with no additional collections returns a clone; subtracting an empty collection also returns a clone.
- Both classes support instance macros; see [Macros](../core/macros.md).

## Related

- [Utilities](index.md)
- [Date/time](datetime.md)
