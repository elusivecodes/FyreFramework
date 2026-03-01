# Collections

`Collection` (`Fyre\Utility\Collection`) is the primary tool for shaping sequences of values with fluent, chainable pipelines. It lives in the utilities layer alongside other small, reusable helpers (see [Utilities](index.md)), and for array-first helpers (dot-path access, flattening, etc.), see [Array Helpers](arrays.md).

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [`Collection` mental model](#collection-mental-model)
  - [Eager vs lazy checkpoints](#eager-vs-lazy-checkpoints)
- [Building pipelines](#building-pipelines)
- [Selecting values with paths](#selecting-values-with-paths)
- [Working with nested data](#working-with-nested-data)
  - [Dot-flattening and value flattening](#dot-flattening-and-value-flattening)
  - [Building and walking trees](#building-and-walking-trees)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Creating collections](#creating-collections)
  - [Transforming sequences](#transforming-sequences)
  - [Selecting and slicing](#selecting-and-slicing)
  - [Extracting, indexing, and grouping](#extracting-indexing-and-grouping)
  - [Searching and predicates](#searching-and-predicates)
  - [Ordering and uniqueness](#ordering-and-uniqueness)
  - [Keys and values](#keys-and-values)
  - [Aggregates and statistics](#aggregates-and-statistics)
  - [Nested structures](#nested-structures)
  - [Materializing and output](#materializing-and-output)
  - [Convenience methods](#convenience-methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Collection` when you want readable, chainable transformations over a sequence of values (especially when the underlying data may be produced lazily). Most transformation methods return a new `Collection`, and values are typically only computed when you iterate or materialize the result.

If you already have a plain array and you want a single operation (or a couple of explicit steps), see [Array Helpers](arrays.md).

## Quick start

```php
use Fyre\Utility\Collection;

$users = new Collection([
    ['id' => 10, 'role' => 'admin', 'profile' => ['email' => 'a@example.com']],
    ['id' => 11, 'role' => 'user', 'profile' => ['email' => 'b@example.com']],
    ['id' => 12, 'role' => 'user', 'profile' => ['email' => 'c@example.com']],
]);

$emails = $users
    ->filter(static fn(array $user): bool => $user['role'] === 'user')
    ->extract('profile.email')
    ->toList();
```

`Collection::range()` is a convenient way to build a numeric sequence:

```php
$values = Collection::range(1, 10)
    ->map(static fn(int $value): int => $value * $value)
    ->take(3)
    ->toList();
```

## `Collection` mental model

🧠 A `Collection` is a sequence wrapper. It can be backed by either:

- an `array` (eager, repeatable), or
- a `Closure` that returns data when iterated (often lazy).

📌 If you use a lazy `Closure` source, make sure it returns a **fresh** `array` or `Iterator` each time the collection is iterated. Reusing the same iterator instance can lead to partially-consumed results.

Example pattern for a lazy source:

```php
$collection = new Collection(static function(): array {
    return [1, 2, 3];
});
```

Most transformation methods (like `map()`, `filter()`, `groupBy()`) return a new `Collection`. Values are typically produced only when you iterate (for example via `foreach`) or materialize (for example via `toArray()`, `toList()`, `toJson()`, `count()`).

### Eager vs lazy checkpoints

Some operations necessarily materialize the collection (they call `toArray()` internally), including:

- `collect()`, `toArray()`, `toList()`, `toJson()`
- `reverse()`, `shuffle()`, `sort()`, `sortBy()`
- `median()` (it sorts), and `nest()` (it builds a tree)

## Building pipelines

📌 Common workflow: start with values, apply a few transformations, then materialize the result.

```php
$values = Collection::range(1, 10)
    ->filter(static fn(int $value): bool => $value % 2 === 0)
    ->map(static fn(int $value): int => $value * $value)
    ->take(3)
    ->toList();
```

Some frequently-used pipeline steps (see the [Method guide](#method-guide) for full signatures and options):

- `map()` transforms items
- `filter()` keeps matching items
- `reject()` drops matching items
- `reduce()` folds items into one value
- `skip()` / `take()` control how much you consume

## Selecting values with paths

Many methods accept a “path” argument (for example `extract()`, `groupBy()`, `indexBy()`, `combine()`):

- `string` paths use dot notation (like `'profile.email'`)
- `array` paths are an explicit list of key segments
- `Closure` paths can compute values from `(item, key)`

Path extraction supports:

- nested arrays
- `ArrayAccess`
- public object properties

Missing segments yield `null`.

Assume `$users` is a list of user rows (arrays/objects) you want to query:

```php
$collection = new Collection($users);

$emails = $collection->extract('profile.email')->toList();
$byId = $collection->indexBy('id')->toArray();
$countByRole = $collection->countBy('role')->toArray();
```

## Working with nested data

### Dot-flattening and value flattening

- `dot()` turns nested arrays/traversables into dot-notated keys.
- `flatten()` flattens nested arrays/traversables into a single list of values.

### Building and walking trees

`nest()` builds a parent/child tree by IDs, placing child items under a configurable nesting key. `listNested()` and `printNested()` are helpers for producing a flat view of that tree.

```php
$categories = new Collection([
    ['id' => 1, 'parent_id' => null, 'name' => 'Root'],
    ['id' => 2, 'parent_id' => 1, 'name' => 'Child'],
]);

$labels = $categories
    ->nest()
    ->printNested('name')
    ->toArray();
```

## Constants

`Collection` exposes sort constants you can use with `sort()` and `sortBy()`:

- `Collection::SORT_LOCALE`
- `Collection::SORT_NATURAL`
- `Collection::SORT_NUMERIC`
- `Collection::SORT_REGULAR`
- `Collection::SORT_STRING`

## Method guide

This section focuses on the methods you’ll use most when working with `Collection`.

```php
use Fyre\Utility\Collection;

$collection = new Collection([1, 2, 3]);
```

### Creating collections

#### **Create an empty collection** (`empty()`)

Creates a `Collection` with no items.

```php
$items = Collection::empty();
```

#### **Create a numeric range** (`range()`)

Creates a collection for a range of numbers (ascending or descending).

Arguments:
- `$start` (`int`): the first value of the sequence.
- `$end` (`int`): the ending value in the sequence.

```php
$values = Collection::range(3, 1)->toList(); // [3, 2, 1]
```

### Transforming sequences

#### **Map items** (`map()`)

Applies a callback to each item and yields the callback result.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns the mapped value.

```php
$values = (new Collection([1, 2, 3]))
    ->map(static fn(int $value): int => $value * 10)
    ->toList(); // [10, 20, 30]
```

#### **Filter items** (`filter()`)

Keeps items where the callback returns `true`.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to keep the item.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->filter(static fn(int $value): bool => $value % 2 === 0)
    ->toList(); // [2, 4]
```

#### **Reject items** (`reject()`)

Drops items where the callback returns `true` (the inverse of `filter()`).

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to reject the item.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->reject(static fn(int $value): bool => $value % 2 === 0)
    ->toList(); // [1, 3]
```

#### **Run a callback for each item** (`each()`)

Executes a callback on each item and returns the same `Collection` instance.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)`.

```php
$sum = 0;
(new Collection([1, 2, 3]))->each(static function(int $value) use (&$sum): void {
    $sum += $value;
});
```

#### **Reduce to a single value** (`reduce()`)

Iteratively reduces the collection to a single value using an accumulator callback.

Arguments:
- `$callback` (`Closure`): Receives `(acc, item, key)` and returns the new accumulator.
- `$initial` (`mixed`): the initial accumulator value.

```php
$sum = (new Collection([1, 2, 3]))
    ->reduce(static fn(int $acc, int $value): int => $acc + $value, 0); // 6
```

#### **Chunk items** (`chunk()`)

Splits items into fixed-size arrays.

Arguments:
- `$size` (`int`): the size of each chunk.
- `$preserveKeys` (`bool`): whether to preserve the original keys.

```php
$chunks = (new Collection([1, 2, 3, 4, 5]))
    ->chunk(2)
    ->toList(); // [[1, 2], [3, 4], [5]]
```

#### **Merge iterables** (`merge()`)

Appends items from one or more iterables to the end of the collection.

Arguments:
- `...$arrays` (`iterable[]`) The iterables to append.

```php
$values = (new Collection([1, 2]))
    ->merge([3, 4])
    ->toList(); // [1, 2, 3, 4]
```

#### **Zip iterables** (`zip()`)

Zips one or more iterables with the collection, yielding arrays of aligned values.

Arguments:
- `...$iterables` (`iterable[]`) The iterables to zip with.

```php
$pairs = (new Collection(['a', 'b']))
    ->zip([1, 2])
    ->toList(); // [['a', 1], ['b', 2]]
```

### Selecting and slicing

#### **Skip items** (`skip()`)

Skips a number of leading items.

Arguments:
- `$length` (`int`): the number of items to skip.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->skip(2)
    ->toList(); // [3, 4]
```

#### **Take items** (`take()`)

Takes a number of leading items. If `$length` is negative, takes from the end.

Arguments:
- `$length` (`int`): the number of items to take.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->take(2)
    ->toList(); // [1, 2]
```

#### **Skip until a condition is met** (`skipUntil()`)

Skips items until the callback returns `true`, then yields from that item onward.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to start yielding.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->skipUntil(static fn(int $value): bool => $value >= 3)
    ->toList(); // [3, 4]
```

#### **Skip while a condition is true** (`skipWhile()`)

Skips items while the callback returns `true`, then yields the remaining items.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to keep skipping.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->skipWhile(static fn(int $value): bool => $value < 3)
    ->toList(); // [3, 4]
```

#### **Take until a condition is met** (`takeUntil()`)

Takes items until the callback returns `true` (the matching item is not included).

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to stop.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->takeUntil(static fn(int $value): bool => $value >= 3)
    ->toList(); // [1, 2]
```

#### **Take while a condition is true** (`takeWhile()`)

Takes items while the callback returns `true`.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to keep taking.

```php
$values = (new Collection([1, 2, 3, 4]))
    ->takeWhile(static fn(int $value): bool => $value < 3)
    ->toList(); // [1, 2]
```

### Extracting, indexing, and grouping

#### **Extract values by path** (`extract()`)

Extracts values from each item using a path.

Arguments:
- `$valuePath` (`array|Closure|string`): the value path (dot notation, key segments, or a callback).

```php
$emails = (new Collection([
    ['profile' => ['email' => 'a@example.com']],
    ['profile' => ['email' => 'b@example.com']],
]))
    ->extract('profile.email')
    ->toList();
```

#### **Index items by a path** (`indexBy()`)

Re-indexes items by the extracted key.

Arguments:
- `$keyPath` (`array|Closure|string`): the key path (dot notation, key segments, or a callback).

```php
$byId = (new Collection([
    ['id' => 10, 'name' => 'A'],
    ['id' => 11, 'name' => 'B'],
]))
    ->indexBy('id')
    ->toArray();
```

#### **Combine keys and values by paths** (`combine()`)

Re-indexes items by a key path, using a value extracted from each item.

Arguments:
- `$keyPath` (`array|Closure|string`): the key path.
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the whole item).

```php
$names = (new Collection([
    ['id' => 10, 'name' => 'A'],
    ['id' => 11, 'name' => 'B'],
]))
    ->combine('id', 'name')
    ->toArray(); // [10 => 'A', 11 => 'B']
```

#### **Group items by a path** (`groupBy()`)

Groups items under the extracted key.

Arguments:
- `$keyPath` (`array|Closure|string`): the key path.

```php
$grouped = (new Collection([
    ['role' => 'admin', 'name' => 'A'],
    ['role' => 'user', 'name' => 'B'],
    ['role' => 'user', 'name' => 'C'],
]))
    ->groupBy('role')
    ->toArray();
```

#### **Count items by a path** (`countBy()`)

Groups items by a key path and yields counts.

Arguments:
- `$keyPath` (`array|Closure|string`): the key path.

```php
$counts = (new Collection([
    ['role' => 'admin'],
    ['role' => 'user'],
    ['role' => 'user'],
]))
    ->countBy('role')
    ->toArray(); // ['admin' => 1, 'user' => 2]
```

### Searching and predicates

#### **Find the first matching item** (`find()`)

Returns the first value that passes the callback, or `null`.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to match.

```php
$value = (new Collection([1, 2, 3]))
    ->find(static fn(int $value): bool => $value > 1); // 2
```

#### **Find the last matching item** (`findLast()`)

Returns the last value that passes the callback, or `null`.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` to match.

```php
$value = (new Collection([1, 2, 3]))
    ->findLast(static fn(int $value): bool => $value > 1); // 3
```

#### **Check whether every item matches** (`every()`)

Returns `true` only if every item passes the callback.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` for matches.

```php
$ok = (new Collection([2, 4, 6]))
    ->every(static fn(int $value): bool => $value % 2 === 0); // true
```

#### **Check whether some items match** (`some()`)

Returns `true` if at least one item passes the callback.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` for matches.

```php
$ok = (new Collection([1, 2, 3]))
    ->some(static fn(int $value): bool => $value % 2 === 0); // true
```

#### **Check whether no items match** (`none()`)

Returns `true` only if no items pass the callback.

Arguments:
- `$callback` (`Closure`): Receives `(item, key)` and returns `true` for matches.

```php
$ok = (new Collection([1, 2, 3]))
    ->none(static fn(int $value): bool => $value < 0); // true
```

#### **Check for an included value** (`includes()`)

Checks whether the collection contains a given value.

Arguments:
- `$value` (`mixed`): the value to search for.

```php
$ok = (new Collection([1, 2, 3]))->includes(2); // true
```

#### **Find the first key for a value** (`indexOf()`)

Returns the first key for the matching value, or `null`.

Arguments:
- `$value` (`mixed`): the value to search for.

```php
$key = (new Collection(['a' => 1, 'b' => 2]))->indexOf(2); // "b"
```

#### **Find the last key for a value** (`lastIndexOf()`)

Returns the last key for the matching value, or `null`.

Arguments:
- `$value` (`mixed`): the value to search for.

```php
$key = (new Collection([1, 2, 1]))->lastIndexOf(1); // 2
```

### Ordering and uniqueness

#### **Sort items** (`sort()`)

Sorts items using either a sort constant (via `sortBy()`) or a comparison callback.

Arguments:
- `$callback` (`Closure|int`): a comparison callback, or a `Collection::SORT_*` constant.
- `$descending` (`bool`): whether to sort in descending order.

```php
$values = (new Collection([3, 1, 2]))
    ->sort(Collection::SORT_NATURAL)
    ->toList(); // [1, 2, 3]
```

#### **Sort by a path** (`sortBy()`)

Sorts items by the extracted value.

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).
- `$sort` (`int`): the sort method (a `Collection::SORT_*` constant).
- `$descending` (`bool`): whether to sort in descending order.

```php
$values = (new Collection([
    ['id' => 2],
    ['id' => 1],
]))
    ->sortBy('id', Collection::SORT_NUMERIC)
    ->toList();
```

#### **Reverse items** (`reverse()`)

Reverses the order of items.

```php
$values = (new Collection([1, 2, 3]))
    ->reverse()
    ->toList(); // [3, 2, 1]
```

#### **Shuffle items** (`shuffle()`)

Randomizes item order.

```php
$values = (new Collection([1, 2, 3]))->shuffle()->toList();
```

#### **Return unique items** (`unique()`)

Returns the unique items based on an extracted value.

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).
- `$strict` (`bool`): whether to compare values strictly.

```php
$values = (new Collection([1, '1', 2]))
    ->unique(null, true)
    ->toList(); // [1, "1", 2]
```

### Keys and values

#### **Get keys** (`keys()`)

Yields the keys from the collection.

```php
$keys = (new Collection(['a' => 1, 'b' => 2]))
    ->keys()
    ->toList(); // ["a", "b"]
```

#### **Get values** (`values()`)

Yields the values from the collection (re-indexed as a list when materialized via `toList()`).

```php
$values = (new Collection(['a' => 1, 'b' => 2]))
    ->values()
    ->toList(); // [1, 2]
```

#### **Swap keys and values** (`flip()`)

Swaps keys and values.

```php
$flipped = (new Collection(['a' => 1, 'b' => 2]))
    ->flip()
    ->toArray(); // [1 => "a", 2 => "b"]
```

### Aggregates and statistics

#### **Sum values** (`sumOf()`)

Returns the total sum of an extracted value.

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).

```php
$sum = (new Collection([1, 2, 3]))->sumOf(); // 6
```

#### **Average values** (`avg()`)

Returns the average of an extracted value (ignoring `null` values).

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).

```php
$avg = (new Collection([1, 2, 3]))->avg(); // 2.0
```

#### **Minimum value** (`min()`)

Returns the minimum extracted value.

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).

```php
$min = (new Collection([3, 1, 2]))->min(); // 1
```

#### **Maximum value** (`max()`)

Returns the maximum extracted value.

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).

```php
$max = (new Collection([3, 1, 2]))->max(); // 3
```

#### **Median value** (`median()`)

Returns the median extracted value (ignoring `null` values).

Arguments:
- `$valuePath` (`array|Closure|string|null`): the value path (defaults to the item itself).

```php
$median = (new Collection([1, 3, 2, 4]))->median(); // 2.5
```

### Nested structures

#### **Dot-flatten nested values** (`dot()`)

Flattens nested arrays/traversables using dot-notated keys.

Arguments:
- `$prefix` (`int|string|null`): a key prefix to prepend.

```php
$flat = (new Collection(['a' => ['b' => 1]]))
    ->dot()
    ->toArray(); // ["a.b" => 1]
```

#### **Flatten nested values** (`flatten()`)

Flattens nested arrays/traversables into a single sequence of values.

Arguments:
- `$maxDepth` (`int`): the maximum depth to flatten.

```php
$values = (new Collection([1, [2, [3]]]))
    ->flatten()
    ->toList(); // [1, 2, 3]
```

#### **Build a parent/child tree** (`nest()`)

Nests child items inside parent items using ID paths.

Arguments:
- `$idPath` (`array|Closure|string`): the ID path (defaults to `'id'`).
- `$parentPath` (`array|Closure|string`): the parent ID path (defaults to `'parent_id'`).
- `$nestingKey` (`string`): the key used for nesting children (defaults to `'children'`).

```php
$tree = (new Collection([
    ['id' => 1, 'parent_id' => null, 'name' => 'Root'],
    ['id' => 2, 'parent_id' => 1, 'name' => 'Child'],
]))
    ->nest()
    ->toArray();
```

#### **Flatten a nested tree** (`listNested()`)

Flattens a nested tree into a linear list.

Arguments:
- `$order` (`string`): the traversal order: `'desc'`, `'asc'`, or `'leaves'`.
- `$nestingKey` (`string`): the key used for nesting children (defaults to `'children'`).

```php
$values = (new Collection([
    ['id' => 1, 'children' => [['id' => 2]]],
]))
    ->listNested('desc')
    ->toList();
```

#### **Format a nested tree as labels** (`printNested()`)

Formats nested items based on depth (typically used after `nest()`).

Arguments:
- `$valuePath` (`array|Closure|string`): the value path of the label.
- `$keyPath` (`array|Closure|string`): the key path used as the yielded key (defaults to `'id'`).
- `$prefix` (`string`): the prefix used to indicate depth (defaults to `'--'`).
- `$nestingKey` (`string`): the key used for nesting children (defaults to `'children'`).

```php
$labels = (new Collection([
    ['id' => 1, 'children' => [['id' => 2]]],
]))
    ->printNested('id')
    ->toArray();
```

### Materializing and output

#### **Materialize as an array** (`toArray()`)

Returns the items in the collection as an array.

```php
$array = (new Collection(['a' => 1]))->toArray();
```

#### **Materialize as a list** (`toList()`)

Returns the values in the collection as a list (re-indexed numerically).

```php
$list = (new Collection(['a' => 1, 'b' => 2]))->toList(); // [1, 2]
```

#### **Convert to JSON** (`toJson()`)

Returns a JSON string representation of the collection.

```php
$json = (new Collection(['a' => 1]))->toJson();
```

#### **Cache computed values** (`cache()`)

Caches computed values so that iterating the resulting collection reuses values.

```php
$cached = Collection::range(1, 3)->cache();
```

#### **Collect computed values** (`collect()`)

Collects computed values into a new, eager collection.

```php
$eager = Collection::range(1, 3)->collect();
```

### Convenience methods

#### **Get the first value** (`first()`)

Returns the first value in the collection, or `null` if empty.

```php
$first = (new Collection([1, 2, 3]))->first(); // 1
```

#### **Get the last value** (`last()`)

Returns the last value in the collection, or `null` if empty.

```php
$last = (new Collection([1, 2, 3]))->last(); // 3
```

#### **Check whether the collection is empty** (`isEmpty()`)

Returns `true` if the collection has no items.

```php
$ok = (new Collection([]))->isEmpty(); // true
```

#### **Count items** (`count()`)

Counts all items in the collection.

```php
$count = (new Collection([1, 2, 3]))->count(); // 3

// Equivalent:
$count = count(new Collection([1, 2, 3])); // 3
```

#### **Join values into a string** (`join()`)

Joins values using a glue string, optionally with a different glue for the final value.

Arguments:
- `$glue` (`string`): the separator used between values.
- `$finalGlue` (`string|null`): the conjunction used between the last two values.

```php
$value = (new Collection(['a', 'b', 'c']))->join(', ', ' and '); // "a, b and c"
```

#### **Pick a random value** (`randomValue()`)

Returns a random item, or `null` if the collection is empty.

```php
$value = (new Collection([1, 2, 3]))->randomValue();
```

#### **Include only specific keys** (`only()`)

Returns a collection with only the specified keys.

Arguments:
- `$keys` (`array`): the keys to include.

```php
$subset = (new Collection(['a' => 1, 'b' => 2]))
    ->only(['b'])
    ->toArray(); // ["b" => 2]
```

#### **Exclude specific keys** (`except()`)

Returns a collection without the specified keys.

Arguments:
- `$keys` (`array`): the keys to exclude.

```php
$subset = (new Collection(['a' => 1, 'b' => 2]))
    ->except(['b'])
    ->toArray(); // ["a" => 1]
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- When constructing a `Collection` with a `Closure`, the closure is expected to return a fresh `array` or `Iterator` each time the collection is iterated. Reusing the same iterator instance can lead to partially-consumed results.
- `count()` fully iterates lazy sources to determine the count.
- Methods that call `toArray()` (including `collect()`, `reverse()`, `shuffle()`, `sort()`, `sortBy()`, `median()`, `nest()`) eagerly materialize the full sequence.
- `includes()` uses strict identity (`===`). If you need loose comparison semantics, use [Array Helpers](arrays.md).
- `unique()` uses loose comparison by default unless `$strict` is `true`.
- Path extraction returns `null` for missing segments (and supports nested arrays, `ArrayAccess`, and public object properties).

## Related

- [Utilities](index.md)
- [Array Helpers](arrays.md)
