# Periods

Use `Period` (`Fyre\Utility\DateTime\Period`) when you need a bounded date/time range at a specific granularity.

Use `PeriodCollection` (`Fyre\Utility\DateTime\PeriodCollection`) when you need to work with multiple ranges as a set.

For single instants (time zones, localization, calendar-aware diffs), see [Date/time](datetime.md).

## Table of Contents

- [Common operations](#common-operations)
- [Method guide](#method-guide)
  - [`Period`](#period)
  - [`PeriodCollection`](#periodcollection)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Common operations

Create a period, iterate it, and subtract a blocked range:

Boundaries matter: periods can include or exclude the start and/or end. If you see off-by-one results when iterating or calling `includes()`, double-check the boundary flags (see [Behavior notes](#behavior-notes)).

```php
use Fyre\Utility\DateTime\Period;

$period = new Period('2026-02-01', '2026-02-05'); // default granularity: "day"

foreach ($period as $date) {
    $iso = $date->toIsoString();
}

$blocked = new Period('2026-02-03', '2026-02-04');
$available = $period->subtract($blocked); // PeriodCollection
```

Work with multiple ranges:

```php
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

$a = new Period('2026-02-01', '2026-02-10');
$b = new Period('2026-02-15', '2026-02-20');

$collection = new PeriodCollection($a, $b);

$sorted = $collection->sort();
$boundaries = $collection->boundaries(); // Period|null
$gaps = $collection->gaps(); // PeriodCollection
```

## Method guide

### `Period`

#### **Get the excluded boundary string** (`getBoundaries()`)

Builds the boundary keyword (`'none'`, `'start'`, `'end'`, `'both'`) from two booleans indicating whether the start/end are included.

Arguments:
- `$includesStart` (`bool`): whether to include the start boundary.
- `$includesEnd` (`bool`): whether to include the end boundary.

```php
$excludeBoundaries = Period::getBoundaries(true, false); // "end"
```

#### **Check whether a period includes a date** (`includes()`)

Checks whether a `DateTime` falls within the included boundaries of the period.

Arguments:
- `$date` (`DateTime`): the date to test.

```php
use Fyre\Utility\DateTime\DateTime;

$ok = $period->includes(new DateTime('2026-02-03')); // true
```

#### **Check whether a period fully contains another** (`contains()`)

Checks whether another period is entirely within this period (using included boundaries).

Arguments:
- `$other` (`Period`): the period to test.

```php
$outer = new Period('2026-02-01', '2026-02-10');
$inner = new Period('2026-02-03', '2026-02-05');

$ok = $outer->contains($inner); // true
```

#### **Check whether periods overlap** (`overlapsWith()`)

Returns whether two periods overlap. A shared boundary instant counts only when that boundary is included by both periods.

Arguments:
- `$other` (`Period`): the period to compare against.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-05', '2026-02-10');

$ok = $a->overlapsWith($b); // true
```

#### **Get the overlap between two periods** (`overlap()`)

Returns a new `Period` representing the overlap, or `null` if no overlap exists.

Arguments:
- `$other` (`Period`): the period to compare against.

```php
$a = new Period('2026-02-01', '2026-02-10');
$b = new Period('2026-02-05', '2026-02-12');

$overlap = $a->overlap($b); // Period|null
```

#### **Get the overlap for multiple periods** (`overlapAll()`)

Returns the overlap of this period with every provided period, or `null` if any comparison has no overlap.

Arguments:
- `$others` (`Period[]`): the periods to compare against.

```php
$a = new Period('2026-02-01', '2026-02-10');
$b = new Period('2026-02-05', '2026-02-12');
$c = new Period('2026-02-08', '2026-02-20');

$overlap = $a->overlapAll($b, $c); // Period|null
```

#### **Get overlaps against multiple periods** (`overlapAny()`)

Returns a `PeriodCollection` containing the overlap of this period with each provided period (skipping non-overlapping periods).

Arguments:
- `$others` (`Period[]`): the periods to compare against.

```php
$base = new Period('2026-02-01', '2026-02-10');

$a = new Period('2026-01-15', '2026-01-20');
$b = new Period('2026-02-05', '2026-02-06');
$c = new Period('2026-02-09', '2026-02-15');

$overlaps = $base->overlapAny($a, $b, $c); // PeriodCollection
```

#### **Get the gap between two periods** (`gap()`)

Returns the gap between two non-overlapping periods as a new `Period`, or `null` if the periods overlap or touch with no gap.

Arguments:
- `$other` (`Period`): the period to compare against.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-10', '2026-02-12');

$gap = $a->gap($b); // Period|null
```

#### **Subtract a period from another** (`subtract()`)

Removes the overlapping part of another period and returns the remaining ranges (0–2) as a `PeriodCollection`.

Arguments:
- `$other` (`Period`): the period to remove.

```php
$a = new Period('2026-02-01', '2026-02-10');
$b = new Period('2026-02-04', '2026-02-06');

$remaining = $a->subtract($b); // PeriodCollection
```

#### **Subtract multiple periods at once** (`subtractAll()`)

Subtracts many periods and returns the remaining ranges as a `PeriodCollection`.

Arguments:
- `$others` (`Period[]`): the periods to remove.

```php
$available = new Period('2026-02-01', '2026-02-10');
$busyA = new Period('2026-02-03', '2026-02-04');
$busyB = new Period('2026-02-07', '2026-02-08');

$remaining = $available->subtractAll($busyA, $busyB);
```

#### **Get the symmetric difference** (`diffSymmetric()`)

Returns the non-overlapping parts of two periods.

Arguments:
- `$other` (`Period`): the period to compare against.

```php
$a = new Period('2026-02-01', '2026-02-10');
$b = new Period('2026-02-05', '2026-02-12');

$diff = $a->diffSymmetric($b); // PeriodCollection
```

#### **Check whether two periods touch** (`touches()`)

Returns `true` when the included start of one period is the same as the included end of the other (at the current granularity).

Arguments:
- `$other` (`Period`): the period to compare against.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-05', '2026-02-10');

$ok = $a->touches($b); // true
```

#### **Get the number of yielded values** (`count()`)

Returns how many `DateTime` values iteration will yield for this period (respecting excluded boundaries).

```php
$a = new Period('2026-02-01', '2026-02-05');
$steps = $a->count(); // 5
```

#### **Get the distance between boundaries** (`length()`)

Returns the distance between the included boundaries in units of the granularity (so a single included instant has a length of `0`).

```php
$a = new Period('2026-02-01', '2026-02-05');
$len = $a->length(); // 4
```

#### **Inspect boundaries and configuration**

| Method | Result |
| --- | --- |
| `start()` / `end()` | the original boundary values supplied to the period |
| `includedStart()` / `includedEnd()` | the effective boundaries after exclusions are applied |
| `includesStart()` / `includesEnd()` | whether each original boundary is included |
| `granularity()` | the unit used for iteration and comparison |

#### **Compare periods and boundary dates**

`equals($other)` checks whether two periods have the same included boundaries at the current granularity.

The remaining comparison methods inspect one included boundary against a `DateTime`:

| Boundary | Equal | Before | Before or equal | After | After or equal |
| --- | --- | --- | --- | --- | --- |
| start | `startEquals()` | `startsBefore()` | `startsBeforeOrEquals()` | `startsAfter()` | `startsAfterOrEquals()` |
| end | `endEquals()` | `endsBefore()` | `endsBeforeOrEquals()` | `endsAfter()` | `endsAfterOrEquals()` |

```php
$period = new Period('2026-02-01', '2026-02-05');

$startsAfterJanuary = $period->startsAfter(new DateTime('2026-01-31'));
$endsBy = $period->endsBeforeOrEquals(new DateTime('2026-02-07'));
```

#### **Create the next period with the same length** (`renew()`)

Creates a new period after the current one with the same length and boundary inclusion.

### `PeriodCollection`

#### **Add periods** (`add()`)

Returns a new collection with the added periods appended.

Arguments:
- `$periods` (`Period[]`): the periods to add.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-10', '2026-02-12');

$collection = new PeriodCollection($a);
$withMore = $collection->add($b);
```

#### **Sort by start date** (`sort()`)

Returns a new collection sorted by each period’s included start timestamp.

```php
$a = new Period('2026-02-10', '2026-02-12');
$b = new Period('2026-02-01', '2026-02-05');

$sorted = new PeriodCollection($a, $b)->sort();
```

#### **Remove duplicates** (`unique()`)

Returns a new collection with duplicate periods removed. Periods are considered duplicates if they are equal according to `Period::equals()`.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-01', '2026-02-05');

$unique = new PeriodCollection($a, $b)->unique();
```

#### **Get collection boundaries** (`boundaries()`)

Returns the minimal `Period` covering all periods in the collection, or `null` if the collection is empty.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-10', '2026-02-12');

$boundaries = new PeriodCollection($a, $b)->boundaries();
```

#### **Find gaps inside boundaries** (`gaps()`)

Returns a new collection containing the uncovered ranges inside `boundaries()`.

```php
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-10', '2026-02-12');

$gaps = new PeriodCollection($a, $b)->gaps();
```

#### **Intersect a period with every element** (`intersect()`)

Intersects one period with every element in the collection and returns only the overlapping parts.

Arguments:
- `$other` (`Period`): the period to intersect with.

```php
$window = new Period('2026-02-04', '2026-02-11');
$a = new Period('2026-02-01', '2026-02-05');
$b = new Period('2026-02-10', '2026-02-12');

$overlaps = new PeriodCollection($a, $b)->intersect($window);
```

#### **Subtract a set of periods** (`subtract()`)

Subtracts every period in another collection from every period in this collection.

Arguments:
- `$others` (`PeriodCollection`): the collection to subtract.

```php
$available = new PeriodCollection(
    new Period('2026-02-01', '2026-02-10')
);

$busy = new PeriodCollection(
    new Period('2026-02-03', '2026-02-04'),
    new Period('2026-02-07', '2026-02-08')
);

$remaining = $available->subtract($busy);
```

#### **Get overlap for multiple collections** (`overlapAll()`)

Returns a new collection containing the overlap of all provided collections (or a clone of the current collection if none are provided).

Arguments:
- `$others` (`PeriodCollection[]`): the collections to compare against.

```php
$a = new PeriodCollection(new Period('2026-02-01', '2026-02-10'));
$b = new PeriodCollection(new Period('2026-02-05', '2026-02-12'));
$c = new PeriodCollection(new Period('2026-02-08', '2026-02-20'));

$overlap = $a->overlapAll($b, $c);
```

## Behavior notes

- `count()` and `length()` answer different questions: `count()` is “how many steps will iteration yield”, while `length()` is the distance between the included boundaries.
- Boundary exclusion affects both iteration and operations like `includes()`; double-check whether you want to exclude `start`, `end`, or both.
- Many period-to-period operations require matching granularities (for example: `overlap()`, `subtract()`, `contains()`, `equals()`). When granularities don’t match, these methods throw a `LogicException`.
- `PeriodCollection` does not automatically sort, merge, or normalize periods. Use `sort()` when order matters.
- `boundaries()` returns `null` for an empty collection.

## Related

- [Utilities](index.md)
- [Date/time](datetime.md)
