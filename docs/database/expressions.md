# Query expressions

Use query expressions when a query needs conditions, functions, aggregates, or window calculations that are clearer than raw SQL.

Expressions are created from the query being compiled, so identifiers use the correct quoting rules and normal values remain bound parameters.

## Table of Contents

- [Start here](#start-here)
- [Expression callbacks](#expression-callbacks)
- [Identifiers and literals](#identifiers-and-literals)
- [Conditions](#conditions)
  - [Comparisons](#comparisons)
  - [Condition groups](#condition-groups)
- [Case expressions](#case-expressions)
- [Functions](#functions)
  - [Scalar functions](#scalar-functions)
  - [Date and time functions](#date-and-time-functions)
  - [Aggregate functions](#aggregate-functions)
  - [Window functions](#window-functions)
  - [Window frames](#window-frames)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Queries expose four main expression helpers:

- `expr()` creates a `ConditionExpression`.
- `case()` creates a `CaseExpression`.
- `func()` returns the query's `FunctionBuilder`.
- `identifier()` and `literal()` mark identifiers and deliberate raw SQL fragments.

Use a callback when the expression is part of the query being built:

```php
use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Query;

$rows = $db->select([
        'customer_id',
        'total' => static fn(Query $query): AggregateExpression => $query->func()
            ->sum('Orders.total'),
    ])
    ->from(['Orders' => 'orders'])
    ->groupBy('Orders.customer_id')
    ->execute()
    ->all();
```

## Expression callbacks

Expression callbacks are evaluated when SQL is compiled. The current query is passed as the first argument and the active `ValueBinder` as the optional second argument:

```php
static fn(Query $query, ValueBinder|null $binder): mixed => ...
```

The binder argument is rarely needed. The query provides the expression helpers and is normally the only argument used.

Callbacks can be used as selected fields, conditions, join conditions, and other values accepted by the query compiler.

## Identifiers and literals

Use normal strings for identifiers in methods that explicitly accept fields, such as `select()`, `groupBy()`, and most `FunctionBuilder` methods. These strings are quoted for the active database handler.

Use `identifier()` when an identifier appears in a general value list where a plain string would otherwise be treated as a bound value:

```php
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Query;

$query = $db->select([
        'display_name' => static fn(Query $query): FunctionExpression => $query->func()
            ->coalesce([
                $query->identifier('Users.nickname'),
                $query->identifier('Users.name'),
                'Unknown',
            ]),
    ])
    ->from(['Users' => 'users']);
```

In this example, the two identifiers are quoted while `Unknown` is treated as a value.

Automatic identifier quoting recognizes common forms such as:

- `column`
- `table.column`
- `table.*`
- simple single-argument functions such as `COUNT(*)`
- the same forms followed by `AS alias`

More complex strings are left unchanged for compatibility with existing SQL expressions. Prefer the expression builders for supported functions. Use `literal()` only when raw SQL is intentional:

```php
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Query;

$query = $db->select([
        'created_date' => static fn(Query $query): LiteralExpression => $query->literal('DATE(created_at)'),
    ])
    ->from('users');
```

Identifiers and raw fragments must be application-controlled. Binding protects values, but it cannot make a user-supplied identifier or SQL fragment safe.

## Conditions

Use `expr()` when condition arrays are not expressive enough or when comparing two fields:

```php
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

$rows = $db->select('*')
    ->from(['Orders' => 'orders'])
    ->where(static fn(Query $query): ConditionExpression => $query->expr()
        ->gte('Orders.total', 100)
        ->isNull('Orders.deleted'))
    ->execute()
    ->all();
```

### Comparisons

`ConditionExpression` provides:

- equality and ordering: `eq()`, `notEq()`, `gt()`, `gte()`, `lt()`, `lte()`
- ranges and patterns: `between()`, `notBetween()`, `like()`, `notLike()`
- sets: `in()`, `notIn()`, `inOrNull()`, `notInOrNull()`
- null checks: `isNull()`, `isNotNull()`
- null-safe equality: `isDistinctFrom()`, `isNotDistinctFrom()`
- field comparison: `equalFields()`
- subqueries: `exists()`, `notExists()`

`in()` and `notIn()` accept either a non-empty array or a `SelectQuery`. Array-style `IN` and `NOT IN` conditions also reject empty arrays. The `inOrNull()` and `notInOrNull()` variants add an `IS NULL` alternative.

Use `compare()` only when a named comparison helper does not fit. Its operator is inserted into the generated SQL, so it must be application-controlled.

### Condition groups

Use `add()`, `and()`, `or()`, and `not()` to build nested condition groups:

```php
$query->where(static fn(Query $query): ConditionExpression => $query->expr()
    ->or(
        $query->expr()->eq('Orders.status', 'pending'),
        $query->expr()->and(
            $query->expr()->eq('Orders.status', 'paid'),
            $query->expr()->isNull('Orders.refunded')
        )
    ));
```

Calling comparison methods on the same expression joins them using that expression's conjunction. `expr()` defaults to `AND`; pass `OR` or call `setConjunction()` to use a different default conjunction.

An empty `ConditionExpression` contributes no predicate and is ignored in condition collections. A join without `using` still requires at least one remaining condition.

## Case expressions

Call `case()` without a value for a searched `CASE` expression:

```php
use Fyre\DB\Expressions\CaseExpression;
use Fyre\DB\Query;

$query = $db->select([
        'status_label' => static fn(Query $query): CaseExpression => $query->case()
            ->when($query->expr()->eq('Users.active', true), 'Active')
            ->else('Inactive'),
    ])
    ->from(['Users' => 'users']);
```

Pass an identifier or another expression to `case()` for a simple `CASE` expression:

```php
$query = $db->select([
        'status_label' => static fn(Query $query): CaseExpression => $query->case(
            $query->identifier('Users.status')
        )
            ->when('active', 'Active')
            ->else('Inactive'),
    ])
    ->from(['Users' => 'users']);
```

Each `when()` call adds a branch. `else()` sets the fallback result.

Searched `CASE` branches also accept condition arrays. A case expression must contain at least one `when()` branch before it is compiled.

## Functions

Use `func()` to build functions without writing handler-specific quoting or function syntax manually.

String arguments documented as fields or expressions are treated as identifiers. General argument arrays, such as those passed to `coalesce()` and `concat()`, treat normal strings as values; wrap field names with `identifier()` in those arrays.

### Scalar functions

The scalar helpers are:

- numeric: `abs()`, `ceil()`, `floor()`, `round()`
- strings: `concat()`, `length()`, `lower()`, `replace()`, `substring()`, `trim()`, `upper()`
- values and conversion: `cast()`, `coalesce()`, `nullIf()`
- JSON: `jsonValue()`

```php
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Query;

$query = $db->select([
        'email' => static fn(Query $query): FunctionExpression => $query->func()
            ->lower('Users.email'),
    ])
    ->from(['Users' => 'users']);
```

### Date and time functions

Date helpers include `dateAdd()`, `dateSub()`, `dateDiff()`, `datePart()`, `extract()`, `dayOfWeek()`, `weekDay()`, and `now()`.

Date parts and interval units accept `day`, `hour`, `minute`, `month`, `second`, `week`, or `year`. `now()` accepts `date`, `datetime`, or `time`.

Pass the two date expressions to `dateDiff()` as an array. `dateAdd()` and `dateSub()` accept the date expression, amount, and interval unit as separate arguments.

```php
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Query;

$query = $db->select([
        'expires' => static fn(Query $query): FunctionExpression => $query->func()
            ->dateAdd('Sessions.created', 1, 'week'),
    ])
    ->from(['Sessions' => 'sessions']);
```

### Aggregate functions

Use `avg()`, `count()`, `max()`, `min()`, and `sum()` for aggregate values. Aggregate expressions also provide:

- `distinct()` to aggregate distinct values
- `filter()` to restrict the rows included in the aggregate
- `over()` to convert the aggregate into a window expression

```php
use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Query;

$query = $db->select([
        'active_total' => static fn(Query $query): AggregateExpression => $query->func()
            ->sum('Orders.total')
            ->filter($query->expr()->eq('Orders.active', true)),
    ])
    ->from(['Orders' => 'orders']);
```

PostgreSQL uses `FILTER (WHERE ...)` for filtered aggregates. Other handlers generate an equivalent conditional aggregate.

### Window functions

Window helpers include:

- ranking: `cumeDist()`, `denseRank()`, `ntile()`, `percentRank()`, `rank()`, `rowNumber()`
- row values: `firstValue()`, `lastValue()`, `nthValue()`, `lag()`, `lead()`

Configure the returned `WindowExpression` with `partitionBy()` and `orderBy()`:

```php
use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Query;

$query = $db->select([
        'previous_total' => static fn(Query $query): WindowExpression => $query->func()
            ->lag('Orders.total', default: 0)
            ->partitionBy('Orders.customer_id')
            ->orderBy(['Orders.created' => 'ASC']),
    ])
    ->from(['Orders' => 'orders']);
```

### Window frames

Use `rows()`, `range()`, or `groups()` to set a window frame. For each boundary:

- `null` means unbounded
- `0` means the current row
- a positive integer is an offset

The first argument is a preceding boundary and the second is a following boundary. The second argument defaults to the current row.

Use `excludeCurrent()`, `excludeGroup()`, or `excludeTies()` to add an exclusion clause when supported by the database.

```php
use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Query;

$query = $db->select([
        'running_total' => static fn(Query $query): WindowExpression => $query->func()
            ->sum('Orders.total')
            ->over()
            ->partitionBy('Orders.customer_id')
            ->orderBy(['Orders.created' => 'ASC'])
            ->rows(null),
    ])
    ->from(['Orders' => 'orders']);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Expressions are compiled for the active database handler, so equivalent expressions can produce different SQL across MySQL, MariaDB, PostgreSQL, and SQLite.
- Database support still applies. In particular, window functions, frame types, exclusions, JSON operations, and casts depend on the database version and selected data types.
- Values remain bound unless they are wrapped in a `LiteralExpression` or supplied through another raw SQL path.
- `COUNT(*)` cannot be combined with `distinct()`; specify a field when counting distinct values.
- Window offsets, bucket counts, substring positions, date parts, interval units, and cast types are validated before SQL is generated.

## Related

- [Database queries](queries.md)
- [Database connections](connections.md)
- [Database types](types.md)
- [Finding Data](../orm/finding.md)
