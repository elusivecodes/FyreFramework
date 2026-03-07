# Array Helpers

`Arr` (`Fyre\Utility\Arr`) is a static array utility class for common transformations, selection helpers, and thin wrappers around built-in PHP array functions (with consistent argument ordering).

If you want fluent, chainable pipelines for sequences (with operations like `map()`, `filter()`, and `reduce()`), use [Collections](collections.md) instead.


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Dot-path helpers](#dot-path-helpers)
  - [Shape helpers](#shape-helpers)
  - [Selecting keys and values](#selecting-keys-and-values)
  - [Searching and matching](#searching-and-matching)
  - [Predicates](#predicates)
  - [Transformations](#transformations)
  - [Set-like operations](#set-like-operations)
  - [Slicing, padding, and chunking](#slicing-padding-and-chunking)
  - [Stack/queue helpers (by reference)](#stackqueue-helpers-by-reference)
  - [Miscellaneous helpers](#miscellaneous-helpers)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `Arr` when you already have a plain PHP array and want a focused, explicit operation: dot-path lookups/updates, flattening, selecting keys/values, searching, and small transformations.

## Quick start

```php
use Fyre\Utility\Arr;

$data = [
    'items' => [
        ['name' => 'A'],
        [],
    ],
];

// Dot-path lookups
$name = Arr::getDot($data, 'items.0.name');          // "A"
$missing = Arr::getDot($data, 'items.1.name', 'N/A'); // "N/A"

// Dot-path updates (with wildcard)
$data = Arr::setDot($data, 'items.*.name', 'Unknown', false);

// Transformations
$ids = Arr::map([10, 20, 30], static fn(int $v, int $k): int => (int) ($v / 10)); // [1, 2, 3]
$nonEmpty = Arr::filter(['a' => 1, 'b' => 0]); // ['a' => 1]
```

## Constants

`Arr` exposes a small set of constants that mirror common PHP flags used by some methods:

- Counting modes for `count()`:
  - `Arr::COUNT_NORMAL`
  - `Arr::COUNT_RECURSIVE`
- Filter callback modes for `filter()`:
  - `Arr::FILTER_BOTH` (value and key)
  - `Arr::FILTER_KEY` (key only)
  - `Arr::FILTER_VALUE` (value only)
- Sort flags for `sort()` and comparison flags for `unique()`:
  - `Arr::SORT_LOCALE`, `Arr::SORT_NATURAL`, `Arr::SORT_NUMERIC`, `Arr::SORT_REGULAR`, `Arr::SORT_STRING`

## Method guide

Examples on this page assume `Arr` refers to `Fyre\Utility\Arr`.

### Dot-path helpers

#### **Retrieve a value by dot-path** (`getDot()`)

Arguments:
- `$array` (`array`): the input array.
- `$key` (`string`): the dot-notated path (for example: `items.0.name`).
- `$default` (`mixed`): the fallback value if the path does not exist.

```php
$value = Arr::getDot(['a' => ['b' => 1]], 'a.b'); // 1
$missing = Arr::getDot(['a' => []], 'a.b', 0);    // 0
```

#### **Check whether a dot-path exists** (`hasDot()`)

Returns `true` only if every segment exists as an array key.

Arguments:
- `$array` (`array`): the input array.
- `$key` (`string`): the dot-notated path (for example: `items.0.name`).

```php
Arr::hasDot(['a' => ['b' => null]], 'a.b'); // true
Arr::hasDot(['a' => []], 'a.b');           // false
```

#### **Set a value by dot-path** (`setDot()`)

Creates intermediate arrays as needed. If `$overwrite` is `false`, an existing final key is left unchanged.

`setDot()` supports a `*` wildcard segment to apply the remaining path to every child at that level (for example: `items.*.name`).

Arguments:
- `$array` (`array`): the input array.
- `$key` (`string`): the dot-notated path (for example: `items.*.name`).
- `$value` (`mixed`): the value to set.
- `$overwrite` (`bool`): whether to overwrite an existing final key.

```php
$data = [
    'items' => [
        ['name' => 'A'],
        [],
    ],
];

$data = Arr::setDot($data, 'items.*.name', 'Unknown', false);
```

#### **Remove a key by dot-path** (`forgetDot()`)

If the path does not exist, the original array is returned unchanged.

Arguments:
- `$array` (`array`): the input array.
- `$key` (`string`): the dot-notated path (for example: `items.0.name`).

```php
$data = Arr::forgetDot(['a' => ['b' => 1]], 'a.b'); // ['a' => []]
```

#### **Pluck dot-path values from rows** (`pluckDot()`)

Extracts the dot-path value from each row and returns a list of values.

Arguments:
- `$arrays` (`array`): the input rows.
- `$key` (`string`): the dot-notated path to extract from each row.

```php
$rows = [
    ['user' => ['id' => 1]],
    ['user' => ['id' => 2]],
];

$ids = Arr::pluckDot($rows, 'user.id'); // [1, 2]
```

### Shape helpers

#### **Flatten nested arrays to dot keys** (`dot()`)

Flattens only nested arrays; non-array values (including objects) are treated as terminal values.

Arguments:
- `$array` (`array`): the input array.
- `$prefix` (`string|null`): optional dot-prefix to apply to all keys.

```php
$flat = Arr::dot(['a' => ['b' => 1, 'c' => 2]]);
// ['a.b' => 1, 'a.c' => 2]
```

#### **Flatten nested arrays to a list** (`flatten()`)

Flattens up to `$maxDepth` levels into a single list of values.

Arguments:
- `$array` (`array`): the input array.
- `$maxDepth` (`int`): how many levels to flatten.

```php
$values = Arr::flatten([1, [2, 3], [4, [5]]], 1); // [1, 2, 3, 4, [5]]
$deep = Arr::flatten([1, [2, 3], [4, [5]]], 3);   // [1, 2, 3, 4, 5]
```

#### **Split keys and values** (`divide()`)

Returns a two-element array: `[keys, values]`.

Arguments:
- `$array` (`array`): the input array.

```php
[$keys, $values] = Arr::divide(['a' => 1, 'b' => 2]); // [['a', 'b'], [1, 2]]
```

#### **Wrap a value as an array** (`wrap()`)

Returns the value unchanged if it is already an array. Returns `[]` for `null`, otherwise returns `[$value]`.

Arguments:
- `$value` (`mixed`): the value to wrap.

```php
Arr::wrap(null);        // []
Arr::wrap('a');         // ['a']
Arr::wrap(['a', 'b']);  // ['a', 'b']
```

#### **Fill an array with a value** (`fill()`)

Creates an array with `$amount` elements, all set to `$value`.

Arguments:
- `$amount` (`int`): the number of elements to insert.
- `$value` (`mixed`): the value to repeat.

```php
$items = Arr::fill(3, 'x'); // ['x', 'x', 'x']
```

#### **Create a range of values** (`range()`)

Creates an array of values from `$start` to `$end` (inclusive).

Arguments:
- `$start` (`float|int|string`): the first value in the sequence.
- `$end` (`float|int|string`): the last value in the sequence.
- `$step` (`float|int`): the increment between values.

```php
$values = Arr::range(1, 5); // [1, 2, 3, 4, 5]
```

### Selecting keys and values

#### **Get all keys** (`keys()`)

Arguments:
- `$array` (`array`): the input array.

```php
$keys = Arr::keys(['a' => 1, 'b' => 2]); // ['a', 'b']
```

#### **Get all values** (`values()`)

Arguments:
- `$array` (`array`): the input array.

```php
$values = Arr::values(['a' => 1, 'b' => 2]); // [1, 2]
```

#### **Keep only the specified keys** (`only()`)

Arguments:
- `$array` (`array`): the input array.
- `$keys` (`array`): the keys to keep.

```php
$subset = Arr::only(['a' => 1, 'b' => 2], ['b']); // ['b' => 2]
```

#### **Exclude the specified keys** (`except()`)

Arguments:
- `$array` (`array`): the input array.
- `$keys` (`array`): the keys to exclude.

```php
$rest = Arr::except(['a' => 1, 'b' => 2], ['b']); // ['a' => 1]
```

#### **Extract a column from rows** (`column()`)

Arguments:
- `$arrays` (`array`): the input rows.
- `$key` (`int|string`): the column key to extract.

```php
$names = Arr::column([['name' => 'A'], ['name' => 'B']], 'name'); // ['A', 'B']
```

#### **Index rows by a column** (`index()`)

Indexes a list of rows by a key from each row.

Arguments:
- `$array` (`array`): the input rows.
- `$key` (`int|string`): the column to use as the new array key.

```php
$rows = [
    ['id' => 10, 'name' => 'A'],
    ['id' => 20, 'name' => 'B'],
];

$byId = Arr::index($rows, 'id');
// [10 => ['id' => 10, 'name' => 'A'], 20 => ['id' => 20, 'name' => 'B']]
```

#### **Combine keys and values** (`combine()`)

Arguments:
- `$keys` (`array`): the keys.
- `$values` (`array`): the values.

```php
$map = Arr::combine(['a', 'b'], [1, 2]); // ['a' => 1, 'b' => 2]
```

#### **Join string values** (`join()`)

Arguments:
- `$array` (`string[]`): the input strings.
- `$separator` (`string`): the separator to join with.

```php
$text = Arr::join(['a', 'b', 'c'], ','); // "a,b,c"
```

### Searching and matching

#### **Check whether a key exists** (`hasKey()`)

Arguments:
- `$array` (`array`): the input array.
- `$key` (`int|string`): the key to check for.

```php
Arr::hasKey(['a' => null], 'a'); // true
```

#### **Check whether a value exists** (`includes()`)

Arguments:
- `$array` (`array`): the input array.
- `$value` (`mixed`): the value to search for.
- `$strict` (`bool`): whether to perform a strict comparison.

```php
Arr::includes([1, '1'], '1');        // true
Arr::includes([1, '1'], '1', true);  // true (strict)
Arr::includes([1], '1', true);       // false (strict)
```

#### **Find the first value matching a predicate** (`find()`)

Returns the first matching value, otherwise `$default`.

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` for a match.
- `$default` (`mixed`): the fallback value when no match is found.

```php
$value = Arr::find([1, 2, 3], static fn(int $v, int $k): bool => $v > 1); // 2
```

#### **Find the last value matching a predicate** (`findLast()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` for a match.
- `$default` (`mixed`): the fallback value when no match is found.

```php
$value = Arr::findLast([1, 2, 3], static fn(int $v, int $k): bool => $v > 1); // 3
```

#### **Find the first key matching a predicate** (`findKey()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` for a match.

```php
$key = Arr::findKey(['a' => 1, 'b' => 2], static fn(int $v, string $k): bool => $v > 1); // 'b'
```

#### **Find the last key matching a predicate** (`findLastKey()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` for a match.

```php
$key = Arr::findLastKey(['a' => 1, 'b' => 2], static fn(int $v, string $k): bool => $v > 1); // 'b'
```

#### **Get the first element** (`first()`)

Arguments:
- `$array` (`array`): the input array.

```php
$first = Arr::first([10, 20]); // 10
```

#### **Get the last element** (`last()`)

Arguments:
- `$array` (`array`): the input array.

```php
$last = Arr::last([10, 20]); // 20
```

#### **Find the first index/key of a value** (`indexOf()`)

Arguments:
- `$array` (`array`): the input array.
- `$value` (`mixed`): the value to search for.
- `$strict` (`bool`): whether to perform a strict comparison.

```php
Arr::indexOf(['a', 'b', 'a'], 'a'); // 0
```

#### **Find the last index/key of a value** (`lastIndexOf()`)

Arguments:
- `$array` (`array`): the input array.
- `$value` (`mixed`): the value to search for.
- `$strict` (`bool`): whether to perform a strict comparison.

```php
Arr::lastIndexOf(['a', 'b', 'a'], 'a'); // 2
```

### Predicates

#### **Check whether every element passes** (`every()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` to pass.

```php
$ok = Arr::every([2, 4, 6], static fn(int $v, int $k): bool => $v % 2 === 0); // true
```

#### **Check whether some elements pass** (`some()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` to pass.

```php
$ok = Arr::some([1, 2, 3], static fn(int $v, int $k): bool => $v > 2); // true
```

#### **Check whether no elements pass** (`none()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns `true` to pass.

```php
$ok = Arr::none([1, 2, 3], static fn(int $v, int $k): bool => $v < 0); // true
```

#### **Check whether a value is an array** (`isArray()`)

Arguments:
- `$value` (`mixed`): the value to test.

```php
Arr::isArray([]);      // true
Arr::isArray('hello'); // false
```

#### **Check whether an array is a list** (`isList()`)

Arguments:
- `$array` (`array`): the array to test.

```php
Arr::isList([10, 20]);         // true
Arr::isList([1 => 'a', 2 => 'b']); // false
```

#### **Count elements** (`count()`)

Arguments:
- `$array` (`array`): the input array.
- `$mode` (`int`): the counting mode (`Arr::COUNT_NORMAL` or `Arr::COUNT_RECURSIVE`).

```php
Arr::count([1, 2, 3]);                   // 3
Arr::count(['a' => ['b' => 1]], Arr::COUNT_RECURSIVE); // 2
```

### Transformations

#### **Map values** (`map()`)

The callback receives `(value, key)` and the result preserves the original keys.

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(value, key)` and returns the mapped value.

```php
$out = Arr::map(['a' => 1, 'b' => 2], static fn(int $v, string $k): int => $v * 10);
// ['a' => 10, 'b' => 20]
```

#### **Filter values** (`filter()`)

When a callback is provided, `filter()` defaults to passing both `(value, key)` to the callback.

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable|null`): Receives `(value, key)` and returns `true` to keep the item.
- `$mode` (`int`): Callback mode (`Arr::FILTER_BOTH`, `Arr::FILTER_KEY`, or `Arr::FILTER_VALUE`).

```php
$out = Arr::filter(['a' => 1, 'b' => 2], static fn(int $v, string $k): bool => $k === 'b');
// ['b' => 2]
```

#### **Reduce to a single value** (`reduce()`)

Arguments:
- `$array` (`array`): the input array.
- `$callback` (`callable`): Receives `(carry, value)` and returns the new carry.
- `$initial` (`mixed`): the initial carry value.

```php
$sum = Arr::reduce([1, 2, 3], static fn(int $carry, int $v): int => $carry + $v, 0); // 6
```

#### **Merge arrays** (`merge()`)

Arguments:
- `$arrays` (`array ...`): the arrays to merge.

```php
$out = Arr::merge(['a' => 1], ['b' => 2]); // ['a' => 1, 'b' => 2]
```

#### **Recursively replace values** (`collapse()`)

Replaces values from later arrays into the first array recursively.

Arguments:
- `$array` (`array`): the input array.
- `$replacements` (`array ...`): the replacement arrays.

```php
$out = Arr::collapse(['a' => ['b' => 1]], ['a' => ['b' => 2, 'c' => 3]]);
// ['a' => ['b' => 2, 'c' => 3]]
```

#### **Reverse elements** (`reverse()`)

Arguments:
- `$array` (`array`): the input array.
- `$preserveKeys` (`bool`): whether to preserve the array keys.

```php
$out = Arr::reverse(['a' => 1, 'b' => 2], true); // ['b' => 2, 'a' => 1]
```

#### **Shuffle elements** (`shuffle()`)

Arguments:
- `$array` (`array`): the input array.

```php
$out = Arr::shuffle([1, 2, 3]);
```

#### **Sort elements** (`sort()`)

Sorts using either a sort flag (default: natural) or a custom comparison closure.

Arguments:
- `$array` (`array`): the input array.
- `$sort` (`Closure|int`): a comparison closure or an `Arr::SORT_*` constant.

```php
$out = Arr::sort(['10', '2']); // ['2', '10'] (natural sort)
```

#### **Remove duplicate values** (`unique()`)

Arguments:
- `$array` (`array`): the input array.
- `$flags` (`int`): the comparison mode (an `Arr::SORT_*` constant).

```php
$out = Arr::unique([1, 1, 2]); // [0 => 1, 2 => 2]
```

### Set-like operations

#### **Difference** (`diff()`)

Arguments:
- `$array` (`array`): the input array.
- `$arrays` (`array ...`): the arrays to compare against.

```php
$out = Arr::diff([1, 2, 3], [2]); // [0 => 1, 2 => 3]
```

#### **Intersection** (`intersect()`)

Arguments:
- `$array` (`array`): the input array.
- `$arrays` (`array ...`): the arrays to compare against.

```php
$out = Arr::intersect([1, 2, 3], [2, 3]); // [1 => 2, 2 => 3]
```

### Slicing, padding, and chunking

#### **Split into chunks** (`chunk()`)

Arguments:
- `$array` (`array`): the input array.
- `$size` (`int`): the chunk size.
- `$preserveKeys` (`bool`): whether to preserve the array keys.

```php
$chunks = Arr::chunk([1, 2, 3, 4], 2); // [[1, 2], [3, 4]]
```

#### **Extract a slice** (`slice()`)

Arguments:
- `$array` (`array`): the input array.
- `$offset` (`int`): the starting offset.
- `$length` (`int|null`): the slice length.
- `$preserveKeys` (`bool`): whether to preserve the array keys.

```php
$out = Arr::slice([1, 2, 3, 4], 1, 2); // [2, 3]
```

#### **Pad to a length** (`pad()`)

Arguments:
- `$array` (`array`): the input array.
- `$size` (`int`): the target size (negative pads to the left).
- `$value` (`mixed`): the pad value.

```php
$out = Arr::pad([1, 2], 4, 0); // [1, 2, 0, 0]
```

#### **Splice by reference** (`splice()`)

Removes a section (starting at `$offset`) and optionally inserts a replacement.

Arguments:
- `$array` (`array`): the input array (passed by reference).
- `$offset` (`int`): the starting offset.
- `$length` (`int|null`): the number of items to remove.
- `$replacement` (`mixed`): the replacement value(s) to insert.

```php
$array = [1, 2, 3, 4];
$removed = Arr::splice($array, 1, 2, ['a']);
// $array is now [1, 'a', 4]
// $removed is [2, 3]
```

### Stack/queue helpers (by reference)

#### **Pop from the end** (`pop()`)

Arguments:
- `$array` (`array`): the input array (passed by reference).

```php
$array = [1, 2];
$last = Arr::pop($array); // 2
```

#### **Push onto the end** (`push()`)

Arguments:
- `$array` (`array`): the input array (passed by reference).
- `$values` (`mixed ...`): the values to push.

```php
$array = [1];
Arr::push($array, 2, 3); // 3 (new count)
```

#### **Shift from the start** (`shift()`)

Arguments:
- `$array` (`array`): the input array (passed by reference).

```php
$array = [1, 2];
$first = Arr::shift($array); // 1
```

#### **Unshift onto the start** (`unshift()`)

Arguments:
- `$array` (`array`): the input array (passed by reference).
- `$values` (`mixed ...`): the values to unshift.

```php
$array = [2, 3];
Arr::unshift($array, 1); // 3 (new count)
```

### Miscellaneous helpers

#### **Get a random value** (`randomValue()`)

Returns `null` for an empty array.

Arguments:
- `$array` (`array`): the input array.

```php
$value = Arr::randomValue(['a', 'b', 'c']);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `filter()` defaults to `Arr::FILTER_BOTH`, so callbacks receive `(value, key)` (unlike PHP’s default `array_filter()` usage which typically passes only the value).
- `setDot()` creates intermediate arrays, supports `*` wildcard segments, and respects `$overwrite` only for the final segment.
- Methods like `sort()`, `shuffle()`, and `reverse()` (when `$preserveKeys` is `false`) produce reindexed lists, so they are best used with list-style arrays.
- `dot()` and `flatten()` only recurse into nested arrays; other value types are treated as terminal values.

## Related

- [Utilities](index.md)
- [Collections](collections.md)
