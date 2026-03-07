# Database queries

Database queries are builder objects created from a `Fyre\DB\Connection`. They compile to SQL, execute with bound values by default, and return a `Fyre\DB\ResultSet` for consuming rows.

## Table of Contents

- [Purpose](#purpose)
- [Query builder basics](#query-builder-basics)
  - [Query types and SQL mapping](#query-types-and-sql-mapping)
  - [Tables and aliases](#tables-and-aliases)
  - [Binding and expressions](#binding-and-expressions)
  - [Condition arrays](#condition-arrays)
  - [Raw SQL fragments](#raw-sql-fragments)
  - [Tail SQL (`epilog()`)](#tail-sql-epilog)
- [Select queries](#select-queries)
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
- [Working with ResultSet](#working-with-resultset)
  - [Indexed access](#indexed-access)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use query builders when you want composable SQL with predictable binding and a consistent result interface, without opting into the ORM layer.

## Query builder basics

Queries are created by a connection and all share the same lifecycle:

1. Build a query (tables, fields, data, conditions).
2. Compile it to SQL and bind values.
3. Execute it through the connection.
4. Consume the returned `ResultSet`.

`Query::execute()` performs the compile/bind/execute flow for you by generating SQL, preparing bindings when enabled, and calling `Connection::execute()`.

Optionally, compile the SQL first with `Query::sql()` (for debugging/logging or when executing manually with `Connection::execute()`).

After a successful `execute()`, the query’s internal “dirty” state is reset, which can matter if you reuse the same query object across multiple operations.

Most examples on this page assume you already have a `$db` (`Connection`) instance.

- If helpers are available, `db()` returns the default connection (see [Database connections](connections.md)).
- Otherwise, resolve a `Connection` from your container and pass it into the code that needs it.

### Query types and SQL mapping

The query objects returned by `Connection` map to the following SQL statement types:

- `Connection::select()` → `SELECT ... FROM ...`
- `Connection::insert()` → `INSERT INTO ... VALUES ...`
- `Connection::update()` → `UPDATE ... SET ...`
- `Connection::delete()` → `DELETE ... FROM ...`
- `Connection::upsert()` → `INSERT INTO ... VALUES ...` with a conflict clause (database-specific)
- `Connection::insertFrom()` → `INSERT INTO ... SELECT ...`
- `Connection::updateBatch()` → a single `UPDATE ... SET ...` statement that typically uses `CASE` expressions to update multiple rows at once

### Tables and aliases

Most query types require a target table. If you forget to set one (for example, calling `execute()` without `from()` / `into()` / `update('...')`), compilation throws an error.

`SelectQuery` is the main exception: it can compile without a `FROM` clause when you’re selecting expressions.

Table aliases are provided using associative arrays like `['Users' => 'users']` (compiled as `users AS Users`), but alias maps are only supported by query types that enable them. In this package:

- `SelectQuery`, `UpdateQuery`, `DeleteQuery`, and `UpdateBatchQuery` support table aliases.
- `InsertQuery`, `UpsertQuery`, and `InsertFromQuery` accept a single table name (no alias map).

`SelectQuery` also supports “virtual tables” (for example, subqueries) in `from()`. Other query types require table names to be strings.

Common query methods (available on all query types):

- `execute(ValueBinder|null $binder = null): ResultSet`
- `sql(ValueBinder|null $binder = null): string`
- `table(array|string $table, bool $overwrite = false): static`
- `getConnection(): Connection`
- `getTable(): array` (the normalized internal table representation)

### Binding and expressions

When a `ValueBinder` is used, values compile into placeholders like `:p0` and the binder stores the corresponding values for execution.

- `Query::execute()` creates a binder automatically when binding is enabled on the query (the default behavior).
- `ValueBinder::bindings()` returns the values keyed by placeholder name (without the leading `:`), suitable for `Connection::execute()`.
- `Connection::execute($sql, $params)` supports both positional parameters (a list) and named parameters (an associative array keyed by placeholder name without `:`).

The compiler also recognizes a few special value types:

- `QueryLiteral` is emitted as raw SQL (no binding).
- `Closure` values are invoked as `fn(Connection $connection, ValueBinder|null $binder): mixed` during compilation.
- `SelectQuery` values compile as subqueries.
- `Fyre\Utility\DateTime\DateTime` values are converted via the connection’s type system.

To embed a raw SQL fragment, create a literal with `$connection->literal()`.

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

The query compiler supports a compact condition-array format (used by `where()` and `having()`). Both methods also accept a raw string, which is treated as a literal SQL fragment and bypasses binding.

- **Equality by default**: `['id' => 5]` compiles as `id = :p0` when a binder is used.
- **Operator suffixes**: append an operator to the key (for example `>=`, `!=`, `LIKE`, `IN`, `IS NOT`).
- `IN` / `NOT IN`: an array value compiles as `IN (...)` by default, or respect an explicit `IN` / `NOT IN` suffix.
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

### Raw SQL fragments

Raw fragments are supported, but they bypass value binding. Prefer bound values wherever possible.

To embed raw SQL:

- Use `$connection->literal()` to inject a safe, explicit `QueryLiteral` fragment (for expressions, column references, functions, etc.).
- Use numeric keys in condition/data arrays for full raw snippets (most flexible, least safe).

Do not put untrusted user input into raw SQL fragments. If you need to compare against a user-provided value, pass it as a normal bound value (or use a binder explicitly) instead.

```php
$rows = $db->select([
        'id',
        'created_at',
        'created_date' => $db->literal('DATE(created_at)'),
    ])
    ->from('users')
    ->where([
        0 => 'archived = 0',
    ])
    ->execute()
    ->all();
```

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

`from()` accepts either a plain table name (for example `'users'`) or an alias map (for example `['u' => 'users']`, which compiles to `users AS u`). Table aliases are only supported by query types that explicitly allow them.

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

- Core composition: `select()`, `distinct()`, `from()`, `join()`, `where()`, `groupBy()`, `having()`, `orderBy()`, `limit()`, `offset()`
- CTEs and set operations: `with()`, `withRecursive()`, `union()`, `unionAll()`, `except()`, `intersect()`

### Joins

`join()` takes an array of join definitions. Each join is keyed by alias (or provides an `alias`) and can specify:

- `table` (defaults to the alias key)
- `type` (defaults to `INNER`)
- `using` (string, optional)
- `conditions` (array, used when `using` is not set)

Join definitions are normalized by alias. If you pass a numerically-indexed list of joins, include an `alias` field in each join (otherwise the alias defaults to `table`).

```php
$rows = $db->select([
        'order_id' => 'Orders.id',
        'total' => 'SUM(Items.price)',
    ])
    ->from(['Orders' => 'orders'])
    ->join([
        'Items' => [
            'table' => 'items',
            'type' => 'LEFT',
            'conditions' => ['Items.order_id = Orders.id'],
        ],
    ])
    ->groupBy('Orders.id')
    ->execute()
    ->all();
```

When joining, you can use either `using` (emitted as-is after `USING`) or `conditions` (compiled via the same condition-array rules as `where()`):

```php
$rows = $db->select('*')
    ->from(['Orders' => 'orders'])
    ->join([
        'Items' => [
            'table' => 'items',
            'type' => 'LEFT',
            'using' => '(order_id)',
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

`Connection::insert()` creates a `Fyre\DB\Queries\InsertQuery`.

This query type compiles to an `INSERT INTO ... VALUES ...` statement.

```php
$db->insert()
    ->into('users')
    ->values([$data])
    ->execute();
```

Key methods you’ll use most often: `into()`, `values()`.

## Update queries

`Connection::update()` creates a `Fyre\DB\Queries\UpdateQuery`.

This query type compiles to an `UPDATE ... SET ...` statement (optionally with `JOIN`, `FROM`, and `WHERE` clauses, depending on the connection features).

```php
$db->update('users')
    ->set(['active' => false])
    ->where(['id IN' => $ids])
    ->execute();
```

Some UPDATE features are connection-dependent:

- `UpdateQuery::from()` throws if `UPDATE ... FROM` is not supported.
- `UpdateQuery::join()` throws if `UPDATE ... JOIN` is not supported.

Key methods you’ll use most often: `set()`, `where()`, plus optional `from()` / `join()` when supported.

## Delete queries

`Connection::delete()` creates a `Fyre\DB\Queries\DeleteQuery`.

This query type compiles to a `DELETE ... FROM ...` statement (optionally with `USING`, `JOIN`, `WHERE`, `ORDER BY`, and `LIMIT` clauses, depending on the connection features).

```php
$db->delete()
    ->from('logs')
    ->where(['created <' => $beforeDateTime])
    ->limit(1000)
    ->execute();
```

Some DELETE features are connection-dependent:

- `DeleteQuery::alias()` throws if deleting by alias is not supported.
- `DeleteQuery::using()` throws if `DELETE ... USING` is not supported.
- `DeleteQuery::join()` throws if `DELETE ... JOIN` is not supported.

Key methods you’ll use most often: `from()`, `where()`, `orderBy()`, `limit()`, plus optional `alias()` / `using()` / `join()` when supported.

## Other write queries

These queries are also created by `Connection` and are useful for bulk operations or database-specific conflict handling.

### Upsert queries

`Connection::upsert()` creates a `Fyre\DB\Queries\UpsertQuery`.

This query type compiles to an insert statement with a database-specific conflict clause (for example, “insert or update on key conflict” semantics).

The `upsert()` argument (`$conflictKeys`) defines which column(s) determine a conflict. The exact SQL generated is database-specific, but conceptually it is “insert, and if these key(s) conflict, update”.

`UpsertQuery::values()` takes an optional `$excludeUpdateKeys` list of columns to skip in the “update-on-conflict” portion (for example, primary keys or immutable fields).

```php
$db->upsert('id')
    ->into('users')
    ->values([$row], 'id')
    ->execute();
```

### Insert-from queries

`Connection::insertFrom()` creates a `Fyre\DB\Queries\InsertFromQuery` for `INSERT ... SELECT` statements.

This query type compiles to `INSERT INTO ... SELECT ...`.

Pass `$columns` as the second argument to `insertFrom()` to explicitly set the insert column list.

```php
$from = $db->select(['id', 'email', 'created'])
    ->from('users')
    ->where(['archived' => false]);

$db->insertFrom($from, ['id', 'email', 'created'])
    ->into('users_archive')
    ->execute();
```

### Update-batch queries

`Connection::updateBatch()` creates a `Fyre\DB\Queries\UpdateBatchQuery`, typically used to update multiple rows using a single statement.

This query type compiles to a single `UPDATE ... SET ...` statement for applying multiple row updates in one query. The exact SQL shape is generator-specific.

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

## Working with ResultSet

`Fyre\DB\ResultSet` is a buffered iterator over a PDO statement. You can iterate it, fetch rows by index, or stream forward one row at a time.

Buffering vs streaming:

- `row()` reads forward and increments the internal index.
- `fetch($index)` may read ahead from the statement to populate the internal buffer up to that index.
- `all()` fetches the remaining rows, buffers them, and frees the underlying cursor.
- `count()` may buffer remaining rows when driver row counts are unreliable.
- `valid()` may also advance the cursor while checking whether another row exists.

```php
$result = $db->select('*')
    ->from('logs')
    ->where(['level' => 'error'])
    ->execute();

$count = 0;
foreach ($result as $row) {
    $count++;
}
```

### Indexed access

`fetch($index)` returns a row by 0-based index. This may read ahead from the underlying statement to populate the internal buffer.

```php
$result = $db->select(['id', 'email'])
    ->from('users')
    ->orderBy(['id' => 'asc'])
    ->execute();

$row = $result->fetch(10);
```

Common `ResultSet` methods:

- `all(): array`
- `first(): array|null`
- `last(): array|null`
- `row(): array|null`
- `fetch(int $index = 0): array|null`
- `columns(): array`
- `columnCount(): int`
- `count(): int`
- `free(): void`

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Casting a query to string uses `Query::__toString()` → `sql()` with no binder, so values are inlined/quoted instead of using placeholders.
- Numeric keys in condition/data arrays are treated as raw SQL fragments and bypass value binding.
- Passing a raw string to `where()` or `having()` is treated as a literal SQL fragment and bypasses binding.
- For null comparisons, use `IS` / `IS NOT` in the condition key (for example `['deleted IS' => null]`).
- `UpdateQuery::from()`, `UpdateQuery::join()`, `DeleteQuery::alias()`, `DeleteQuery::using()`, and `DeleteQuery::join()` can throw when the underlying connection does not support the feature.
- `ResultSet::count()` may buffer remaining rows when the driver’s `rowCount()` is unreliable; `ResultSet::valid()` may also advance the cursor to populate the buffer.

## Related

- [Database connections](connections.md)
- [Database types](types.md)
- [Finding Data](../orm/finding.md)
