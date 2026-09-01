# Database queries

Use query builders when you want to write SQL without moving up to the ORM layer.

Start with a `Connection`, build the query you need, then call `execute()`.

## Table of Contents

- [Build and execute queries](#build-and-execute-queries)
  - [Choose a query type](#choose-a-query-type)
  - [Tables and aliases](#tables-and-aliases)
  - [Binding and expressions](#binding-and-expressions)
  - [Condition arrays](#condition-arrays)
  - [Raw SQL fragments](#raw-sql-fragments)
  - [Optimizer hints](#optimizer-hints)
  - [Tail SQL (`epilog()`)](#tail-sql-epilog)
- [Select queries](#select-queries)
  - [Pagination](#pagination)
  - [Per-group limits](#per-group-limits)
  - [Joins](#joins)
  - [Common table expressions (WITH)](#common-table-expressions-with)
  - [Subqueries](#subqueries)
  - [Unions](#unions)
- [Insert queries](#insert-queries)
- [Update queries](#update-queries)
- [Delete queries](#delete-queries)
- [Other write queries](#other-write-queries)
  - [Upsert queries](#upsert-queries)
  - [Insert-from queries](#insert-from-queries)
  - [Update-batch queries](#update-batch-queries)
- [Working with result sets](#working-with-result-sets)
  - [Indexed access](#indexed-access)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Build and execute queries

Most examples on this page assume `$db` is a `Connection`. Use `db()` for the default configured connection or see [Database connections](connections.md) for other ways to resolve one.

Queries are created by a connection and all share the same lifecycle:

1. Build a query (tables, fields, data, conditions).
2. Compile it to SQL and bind values.
3. Execute it through the connection.
4. Consume the returned `ResultSet`.

`Query::execute()` performs the compile/bind/execute flow for you by generating SQL, preparing bindings, and calling `Connection::execute()`.

Call `Query::sql()` when you need to inspect the compiled SQL or execute it manually with `Connection::execute()`.

### Choose a query type

Choose the builder that matches the statement you need:

| Connection method | Query | SQL operation |
| --- | --- | --- |
| `select()` | `SelectQuery` | `SELECT` |
| `insert()` | `InsertQuery` | `INSERT ... VALUES` |
| `update()` | `UpdateQuery` | `UPDATE` |
| `delete()` | `DeleteQuery` | `DELETE` |
| `upsert()` | `UpsertQuery` | driver-specific insert-or-update |
| `insertFrom()` | `InsertFromQuery` | `INSERT ... SELECT` |
| `updateBatch()` | `UpdateBatchQuery` | update several rows in one statement |

### Tables and aliases

Most query types require a target table. If you forget to set one (for example, calling `execute()` without `from()` / `into()` / `update('...')`), compilation throws an error.

`SelectQuery` is the main exception: it can compile without a `FROM` clause when you’re selecting expressions.

Table aliases are provided using associative arrays like `['Users' => 'users']` (compiled as the driver-quoted equivalent of `users AS Users`), but alias maps are only supported by query types that enable them. In this package:

- `SelectQuery`, `UpdateQuery`, `DeleteQuery`, and `UpdateBatchQuery` support table aliases.
- `InsertQuery`, `UpsertQuery`, and `InsertFromQuery` accept a single table name (no alias map).

`SelectQuery` also supports “virtual tables” (for example, subqueries) in `from()`. Other query types require table names to be strings.

All query types provide `execute()`, `sql()`, `getConnection()`, and expression helpers such as `expr()`, `case()`, `func()`, `identifier()`, and `literal()`. They also support `table()` and `hint()` where the active driver permits the requested behavior.

### Binding and expressions

Queries compile values into placeholders like `:p0`, and the binder stores the corresponding values for execution.

- `Query::execute()` creates a binder automatically.
- `ValueBinder::bindings()` returns the values keyed by placeholder name (without the leading `:`), suitable for `Connection::execute()`.
- `Connection::execute($sql, $params)` supports both positional parameters (a list) and named parameters (an associative array keyed by placeholder name without `:`).

The compiler also recognizes a few special value types:

- `LiteralExpression` is emitted as raw SQL (no binding).
- `Closure` values are invoked as `fn(Query $query, ValueBinder|null $binder): mixed` during compilation.
- `SelectQuery` values compile as subqueries.
- `Fyre\Utility\DateTime\DateTime` values are converted via the connection’s type system.

To embed a raw SQL fragment, create a literal with `$query->literal()`.

Identifier strings that match the supported syntax are quoted using the current connection's rules. This includes columns, qualified columns, wildcards, simple single-argument functions, and optional `AS` aliases. More complex strings are left unchanged for compatibility with existing SQL expressions.

Use `$query->identifier()` when a general expression value is specifically an identifier, and `$query->literal()` when it is deliberately raw SQL. Identifiers and raw fragments must be application-controlled; binding does not make user-supplied identifiers or SQL safe.

For conditions, cases, functions, aggregates, and windows, see [Query expressions](expressions.md).

Example compiling with an explicit binder:

```php
use Fyre\DB\ValueBinder;

$query = $db->select('*')
    ->from('users')
    ->where(['id' => 42]);

$binder = new ValueBinder();
$sql = $query->sql($binder);
$bindings = $binder->bindings();
```

`$sql` contains placeholders like `:p0`, while `$bindings` contains the values to bind.

### Condition arrays

The query compiler supports a compact condition-array format (used by `where()` and `having()`). Both methods also accept a `ConditionExpression`, a closure returning an expression, or a raw string. Raw strings are treated as literal SQL fragments and bypass the normal parameter binding path.

- **Equality by default**: `['id' => 5]` compiles as the driver-quoted equivalent of `id = :p0`.
- **Operator suffixes**: append an operator to the key (for example `>=`, `!=`, `LIKE`, `IN`, `IS NOT`).
- `IN` / `NOT IN`: an array value compiles as `IN (...)` by default, or respects an explicit `IN` / `NOT IN` suffix.
- **Logical groups**: use `['and' => [...]]`, `['or' => [...]]`, `['not' => [...]]` (nestable).
- **Raw fragments**: numeric keys are treated as raw expressions and are not parameterized.

Null handling is explicit: if you want `IS NULL` / `IS NOT NULL`, include `IS` / `IS NOT` in the key (for example `['deleted IS' => null]`).

Example nesting logical groups and using `IS NULL`:

```php
$rows = $db->select('*')
    ->from('users')
    ->where([
        'active' => true,
        'or' => [
            ['deleted IS' => null],
            ['not' => ['status' => 'banned']],
        ],
    ])
    ->execute()
    ->all();
```

A closure receives the current query, so it can build the condition with the query expression API:

```php
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

$rows = $db->select('*')
    ->from('users')
    ->where(static fn(Query $query): ConditionExpression => $query->expr()
        ->eq('active', true))
    ->execute()
    ->all();
```

### Raw SQL fragments

Raw fragments are supported, but they bypass value binding. Prefer bound values wherever possible.

To embed raw SQL:

- Use `$query->literal()` to inject a safe, explicit `LiteralExpression` fragment (for expressions, column references, functions, etc.).
- Use numeric keys in condition/data arrays for full raw snippets (most flexible, least safe).

Do not put untrusted user input into raw SQL fragments. If you need to compare against a user-provided value, pass it as a normal bound value (or use a binder explicitly) instead.

```php
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Query;

$rows = $db->select([
        'id',
        'created_at',
        'created_date' => static fn(Query $query): LiteralExpression => $query->literal('DATE(created_at)'),
    ])
    ->from('users')
    ->where([
        'archived = 0',
    ])
    ->execute()
    ->all();
```

### Optimizer hints

Use `hint()` to add database-specific optimizer hints immediately after the statement keyword:

```php
$rows = $db->select('*')
    ->from('users')
    ->where(['active' => true])
    ->hint('MAX_EXECUTION_TIME(1000)')
    ->execute()
    ->all();
```

Multiple hints are combined into one optimizer comment. Repeated calls merge hints by default; pass `true` as the second argument to overwrite them.

Optimizer hints are supported by MySQL and MariaDB 12 or newer. Other connections throw a `BadMethodCallException`. Vendor-specific hint syntax is not validated by the query builder, so hints must be trusted, application-controlled strings.

### Tail SQL (`epilog()`)

`epilog()` appends raw SQL at the end of the compiled statement. A common use is row locking with `FOR UPDATE`.

Because `epilog()` is raw SQL, keep it to trusted, static strings (never concatenate untrusted input into it).

```php
$row = $db->select('*')
    ->from('users')
    ->where(['id' => $id])
    ->epilog('FOR UPDATE')
    ->execute()
    ->first();
```

## Select queries

`Connection::select()` creates a `Fyre\DB\Queries\SelectQuery`.

This query type compiles to a `SELECT` statement (optionally with `WITH`, `JOIN`, `WHERE`, `GROUP BY`, `HAVING`, `ORDER BY`, and `LIMIT/OFFSET` clauses).

`orderBy()` accepts either a string (for example `'id DESC'`) or an array (for example `['id' => 'desc']`). Use the array form when you want to consistently separate field names from sort direction.

`from()` accepts either a plain table name (for example `'users'`) or an alias map (for example `['u' => 'users']`, which compiles as the driver-quoted equivalent of `users AS u`). Table aliases are only supported by query types that explicitly allow them.

```php
$rows = $db->select(['id', 'email'])
    ->from('users')
    ->where(['active' => true])
    ->orderBy(['id' => 'desc'])
    ->limit(50)
    ->execute()
    ->all();
```

Key methods you’ll use most often:

- Core composition: `select()`, `distinct()`, `from()`, `join()`, `where()`, `groupBy()`, `having()`, `orderBy()`, `limit()`, `offset()`, `groupLimit()`
- CTEs and set operations: `with()`, `withRecursive()`, `union()`, `unionAll()`, `except()`, `intersect()`

### Pagination

Select queries provide three lazy pagination strategies. Each paginator snapshots the query,
replaces any existing `LIMIT`/`OFFSET`, and only executes when items or related metadata are read.
The original query is not modified.

- `paginate(int $page = 1, int $perPage = 20)` returns a `Page`. It fetches at most `perPage + 1` rows in one query to expose `hasNext()` without calculating a total.
- `paginateWithTotal(int $page = 1, int $perPage = 20)` returns a `PageWithTotal`. It runs an item query and a separate count query, exposing `totalItems()` and `totalPages()`.
- `paginateByCursor(string|null $cursor = null, int $perPage = 20)` returns a `CursorPage`. It uses ordered field values instead of an offset and supports both next and previous cursors.

Use pagination with totals when the UI needs an exact total or numbered final page. Use regular
pagination when numbered navigation is useful but a count would be expensive. Use cursor pagination
for stable traversal through large or frequently changing result sets.

Pagination cannot be applied to a query using `groupLimit()`. Cursor pagination supports `DISTINCT`
when all ordered fields are explicitly selected, but does not support `UNION`, `UNION ALL`,
`INTERSECT`, or `EXCEPT` queries.

```php
$page = $db->select('*')
    ->from('users')
    ->where(['active' => true])
    ->orderBy(['id' => 'ASC'])
    ->paginate(page: 2, perPage: 25);

$pageWithTotal = $db->select('*')
    ->from('users')
    ->where(['active' => true])
    ->orderBy(['id' => 'ASC'])
    ->paginateWithTotal(page: 2, perPage: 25);

$cursorPage = $db->select('*')
    ->from('users')
    ->where(['active' => true])
    ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
    ->paginateByCursor(cursor: $cursor, perPage: 25);
```

`Page` and `PageWithTotal` expose `items()`, `count()`, `currentPage()`, `perPage()`,
`firstItem()`, `lastItem()`, `hasNext()`, `hasPrevious()`, `nextPage()`, and
`previousPage()`. Their JSON output places the rows in `data` and metadata in `pagination`.
Only `PageWithTotal` exposes exact total metadata.

`CursorPage` exposes `items()`, `count()`, `currentCursor()`, `perPage()`, `hasNext()`,
`hasPrevious()`, `nextCursor()`, and `previousCursor()`. Pass `nextCursor()` or
`previousCursor()` back into `paginateByCursor()` to move in that direction. Its JSON pagination
metadata contains `perPage`, `nextCursor`, and `previousCursor`.

Cursor ordering has a stricter contract:

- The query must have an `ORDER BY` made from simple field names, or selected aliases that resolve
  to fields or value expressions, with `ASC` or `DESC` directions.
- Ordered fields are selected internally and must contain non-null scalar database values. The
  internal fields are removed from returned results. The final ordered field should be unique; a
  primary key is the usual tie-breaker.
- Cursors are URL-safe encoded navigation state, not encrypted data. Their values are still bound as query parameters when used.
- A cursor is tied to its ordering and is rejected if reused with a different ordered field set or direction.

Selected aliases use the alias for ordering and cursor validation while cursor comparisons use the
underlying field or expression:

```php
$cursorPage = $db->select([
        'sort_id' => 'users.id',
        'name' => 'users.name',
    ])
    ->from('users')
    ->orderBy(['sort_id' => 'ASC'])
    ->paginateByCursor(cursor: $cursor, perPage: 25);
```

Ordered fields that are already selected with an explicit alias are reused for cursor values.
Function and other value-expression aliases are supported; subquery aliases are not.

### Per-group limits

Use `groupLimit()` to apply a limit and offset to each distinct value of a field. The query order determines which rows are selected from each group:

```php
$posts = $db->select([
        'Posts.id',
        'Posts.user_id',
    ])
    ->from([
        'Posts' => 'posts',
    ])
    ->orderBy([
        'Posts.id' => 'DESC',
    ])
    ->groupLimit(3, 'Posts.user_id')
    ->execute()
    ->all();
```

Call `groupLimit()` without arguments to clear the configuration. Per-group limits use a window query and cannot be combined with `DISTINCT` or `UNION` queries.

### Joins

`join()` takes an array of join definitions. Each join is keyed by alias (or provides an `alias`) and can specify:

- `table` (defaults to the alias key)
- `type` (defaults to `INNER`)
- `using` (string, optional)
- `conditions` (array, `Closure`, or `ConditionExpression`; used when `using` is not set)

Join definitions are normalized by alias. If you pass a numerically-indexed list of joins, include an `alias` field in each join (otherwise the alias defaults to `table`).

```php
use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

$rows = $db->select([
        'order_id' => 'Orders.id',
        'total' => static fn(Query $query): AggregateExpression => $query->func()
            ->sum('Items.price'),
    ])
    ->from(['Orders' => 'orders'])
    ->join([
        'Items' => [
            'table' => 'items',
            'type' => 'LEFT',
            'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                ->equalFields('Items.order_id', 'Orders.id'),
        ],
    ])
    ->groupBy('Orders.id')
    ->execute()
    ->all();
```

When joining, you can use either `using` (wrapped in parentheses after `USING`) or `conditions` (compiled via the same condition-array rules as `where()`):

```php
$rows = $db->select('*')
    ->from(['Orders' => 'orders'])
    ->join([
        'Items' => [
            'table' => 'items',
            'type' => 'LEFT',
            'using' => 'order_id',
        ],
    ])
    ->execute()
    ->all();
```

### Common table expressions (WITH)

Use `with()` (or `withRecursive()`) to prepend a `WITH` clause. Common table expressions are provided as an array mapping the CTE name to a `SelectQuery` (or other supported SQL expression value).

```php
$recentUsers = $db->select(['id', 'email', 'created'])
    ->from(['Users' => 'users'])
    ->where(['Users.created >=' => $minCreated]);

$rows = $db->select('*')
    ->with(['RecentUsers' => $recentUsers])
    ->from(['Users' => 'RecentUsers'])
    ->orderBy(['Users.created' => 'desc'])
    ->execute()
    ->all();
```

### Subqueries

If you pass a `SelectQuery` as a value in conditions (or other places that accept expressions), it compiles as a subquery.

```php
$successfulLogins = $db->select('user_id')
    ->from('logins')
    ->where(['success' => true]);

$rows = $db->select(['id', 'email'])
    ->from('users')
    ->where(['id IN' => $successfulLogins])
    ->execute()
    ->all();
```

### Unions

Use `union()`, `unionAll()`, `except()`, and `intersect()` to combine compatible `SELECT` statements.

```php
$current = $db->select('email')
    ->from(['Users' => 'users']);

$archived = $db->select('email')
    ->from(['UsersArchive' => 'users_archive']);

$rows = $current
    ->unionAll($archived)
    ->execute()
    ->all();
```

## Insert queries

Use `insert()` with `into()` and `values()` to add one or more rows:

```php
$db->insert()
    ->into('users')
    ->values([$data])
    ->execute();
```

## Update queries

Use `update()` with `set()` and `where()` to change matching rows:

```php
$db->update('users')
    ->set(['active' => false])
    ->where(['id IN' => $ids])
    ->execute();
```

Additional update clauses are connection-dependent:

- `UpdateQuery::from()` throws if `UPDATE ... FROM` is not supported.
- `UpdateQuery::join()` throws if `UPDATE ... JOIN` is not supported.

## Delete queries

Use `delete()` with `from()` and `where()` to remove matching rows:

```php
$db->delete()
    ->from('logs')
    ->where(['created <' => $beforeDateTime])
    ->execute();
```

Additional delete clauses are connection-dependent:

- `DeleteQuery::alias()` throws if deleting by alias is not supported.
- `DeleteQuery::using()` throws if `DELETE ... USING` is not supported.
- `DeleteQuery::join()` throws if `DELETE ... JOIN` is not supported.
- `DeleteQuery::orderBy()` throws if `DELETE ... ORDER BY` is not supported.
- `DeleteQuery::limit()` throws if `DELETE ... LIMIT` is not supported.

## Other write queries

These queries are also created by `Connection` and are useful for bulk operations or database-specific conflict handling.

### Upsert queries

Use `upsert($conflictKeys)` to insert rows or update them when the specified key conflicts.

- On PostgreSQL and SQLite, `conflictKeys` is used to build the `ON CONFLICT (...)` target.
- On MySQL, `conflictKeys` is ignored for SQL generation because MySQL uses `ON DUPLICATE KEY UPDATE`.

`UpsertQuery::values()` takes an optional `$excludeUpdateKeys` list of columns to skip in the “update-on-conflict” portion (for example, primary keys or immutable fields). `conflictKeys` are always excluded from the update set, even if you do not include them explicitly in `$excludeUpdateKeys`.

```php
$db->upsert('id')
    ->into('users')
    ->values([$row], 'id')
    ->execute();
```

### Insert-from queries

Use `insertFrom($query, $columns)` for `INSERT ... SELECT`. The optional column list explicitly sets the target columns:

```php
$from = $db->select(['id', 'email', 'created'])
    ->from('users')
    ->where(['archived' => false]);

$db->insertFrom($from, ['id', 'email', 'created'])
    ->into('users_archive')
    ->execute();
```

### Update-batch queries

Use `updateBatch()` to apply several row updates in one statement. The exact SQL shape is driver-specific.

The `$keys` argument to `UpdateBatchQuery::set($data, $keys)` defines which column(s) identify each row being updated. These key columns are used to:

- match each input row to a target row in the database, and
- build the `WHERE` clause that restricts the update to only the key values present in `$data`.

`UpdateBatchQuery` also has a couple practical implications:

- The set of updatable columns is taken from the first row in `$data` (excluding `$keys`). Keep `$data` rows structurally consistent.
- If a particular row omits a column that is being updated, that row keeps its existing value for that column (the compiled `CASE` uses `ELSE <column>`).

```php
$db->updateBatch('users')
    ->set($rows, 'id')
    ->execute();
```

## Working with result sets

Database queries return a `ResultSet`. PDO-backed results contain array rows, while `DecoratedResultSet` lazily maps rows to another type. Both can be iterated, fetched by index, or consumed as an array.

Buffering vs streaming:

- `row()` reads forward and increments the internal index.
- `fetch($index)` may read ahead from the statement to populate the internal buffer up to that index.
- `all()` fetches the remaining rows, frees the underlying cursor, and returns the available non-null rows.
- `count()` may buffer remaining rows when driver row counts are unreliable.
- `valid()` may also advance the cursor while checking whether another row exists.

```php
$result = $db->select('*')
    ->from('logs')
    ->where(['level' => 'error'])
    ->execute();

$messages = [];
foreach ($result as $row) {
    $messages[] = $row['message'];
}
```

### Indexed access

`fetch($index)` returns a row by zero-based index and may read ahead to populate the internal buffer.

```php
$result = $db->select(['id', 'email'])
    ->from('users')
    ->orderBy(['id' => 'asc'])
    ->execute();

$row = $result->fetch(10);
```

| Method | Purpose |
| --- | --- |
| `all()` | consume the remaining rows as an array |
| `first()`, `last()` | return the first or last row |
| `row()` | read the next row |
| `fetch($index = 0)` | return a row by index |
| `decorate($decorator, $consume = true)` | lazily map rows into another type |
| `columns()`, `columnCount()` | inspect result columns |
| `count()` | count source rows, buffering when required by the driver |
| `free()` | release the underlying cursor |

`decorate()` returns a new result set and applies its callback lazily, at most once per row.
Decorators can change the row type and can be chained:

```php
$names = $result
    ->decorate(static fn(array $row): User => new User($row))
    ->decorate(static fn(User $user): string => $user->name)
    ->all();
```

By default, each mapped row is removed from the wrapped result, and releasing the decorated result
also releases the wrapped result. Pass `consume: false` to preserve the wrapped result instead.
Decorated result sets delegate counting and column metadata to the wrapped result.

Calling `clearBuffer()` releases buffered rows throughout a consuming decorator chain while
retaining their indexes as sparse gaps. Cleared rows are omitted from `all()`, while the remaining
rows keep their original indexes. `count()` continues to report the total number of source rows.
Decorator callbacks should therefore return non-null values.

## Behavior notes

A few behaviors are worth keeping in mind:

- Casting a query to string uses `Query::__toString()` → `sql()` with no binder, so values are inlined/quoted instead of using placeholders.
- Identifier strings are quoted only when they match the supported identifier forms; do not use query identifiers as a sanitization mechanism for user input.
- Numeric keys in condition/data arrays are treated as raw SQL fragments and bypass value binding.
- Passing a raw string to `where()` or `having()` is treated as a literal SQL fragment and bypasses binding.
- For null comparisons, use `IS` / `IS NOT` in the condition key (for example `['deleted IS' => null]`).
- `UpdateQuery::from()`, `UpdateQuery::join()`, `DeleteQuery::alias()`, `DeleteQuery::using()`, `DeleteQuery::join()`, `DeleteQuery::orderBy()`, and `DeleteQuery::limit()` can throw when the underlying connection does not support the feature.
- `ResultSet::count()` may buffer remaining rows when the driver’s `rowCount()` is unreliable; `ResultSet::valid()` may also advance the cursor to populate the buffer.

## Related

- [Database connections](connections.md)
- [Query expressions](expressions.md)
- [Database types](types.md)
- [Finding Data](../orm/finding.md)
