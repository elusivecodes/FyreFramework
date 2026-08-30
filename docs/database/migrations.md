# Database Migrations

Use migrations when you want database changes to be versioned, repeatable, and applied in order.

In most applications you write migration classes and run them through the console commands.

## Table of Contents

- [Start here](#start-here)
- [Migration workflow](#migration-workflow)
- [Writing migrations](#writing-migrations)
- [Migration discovery](#migration-discovery)
  - [Naming rules](#naming-rules)
  - [Discovery and ordering](#discovery-and-ordering)
- [Running migrations](#running-migrations)
  - [Via console commands](#via-console-commands)
  - [Planning and status](#planning-and-status)
  - [Dry runs](#dry-runs)
  - [Migrate](#migrate)
  - [Rollback](#rollback)
- [Migration history](#migration-history)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In a typical application:

1. Write migrations as `Migration_*` classes.
2. Make sure the runner knows which namespace to scan.
3. Run `db:migrate` or call `migrate()` from code.

Each connection keeps its own migration history in the `migrations` table, so you can run `migrate()` repeatedly without reapplying the same migration.

Minimal example running migrations from code:

```php
use Fyre\DB\Migration\MigrationRunner;

$runner = app(MigrationRunner::class);

$runner->migrate();
```

Migration execution is not automatically wrapped in a transaction. If you need all-or-nothing behavior (and your driver supports transactional DDL), wrap the DDL in a transaction inside your migration’s `up()` / `down()` methods, or design migrations to be safe to rerun after a partial failure.

Example (inside a migration):

```php
public function up(): void
{
    $this->forge->getConnection()->transactional(function(): void {
        $this->forge->createTable(
            'roles',
            [
                'name' => ['length' => 100],
            ],
            [
                'name' => ['unique' => true],
            ]
        );
    });
}
```

## Migration workflow

Migrations sit on top of [Forge](forge.md): you describe the change in a migration class, and Forge executes the DDL for the current connection driver.

Most migration work comes down to three pieces:

- `Migration` is the base class you extend to define changes.
- `MigrationRunner` discovers migrations and runs `up()` / `down()`.
- `MigrationHistory` stores applied migrations for a connection.

Typical workflow:

- write migrations as classes that extend `Migration`
- register namespaces for discovery on `MigrationRunner`
- run `migrate()` and `rollback()` as needed

## Writing migrations

Create migrations as classes extending `Migration`. Implement:

- `up()` to apply changes
- `down()` to roll them back (optional, but recommended)

Within a migration, the current `Forge` instance is available as `$this->forge`.

For DDL operations and options, see [Forge](forge.md).

Example migration class

```php
use Fyre\DB\Migration\Migration;

class Migration_20240201_CreateRoles extends Migration
{
    public function up(): void
    {
        $this->forge->createTable(
            'roles',
            [
                'name' => ['length' => 100],
            ],
            [
                'name' => ['unique' => true],
            ]
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('roles');
    }
}
```

## Migration discovery

`MigrationRunner` discovers migrations by scanning configured namespaces for migration files and loading migration classes from them.

### Naming rules

A migration must:

- live in a file named like `Migration_*.php`
- define a class whose short name starts with `Migration_`
- extend `Migration`
- be non-abstract

`MigrationRunner` derives the **migration name** from the class short name after the `Migration_` prefix (see the example in [Writing migrations](#writing-migrations)).

Use `MigrationRunner::addNamespace()` to register namespaces to scan.

### Discovery and ordering

Discovery behavior:

- each configured namespace is searched for files matching `Migration_*.php`
- each discovered class is checked to be a subclass of `Migration` and non-abstract
- migration names are derived by stripping the `Migration_` prefix from the class short name
- if multiple discovered classes produce the same migration name, the later discovery wins
- migrations are sorted by migration name using natural sorting before execution

## Running migrations

`MigrationRunner` applies migrations in order and records execution in `MigrationHistory`. Execution is not automatically wrapped in a transaction.

### Via console commands

In most apps, migrations are run through the console subsystem rather than directly calling `MigrationRunner` methods.

Use the built-in database migration commands:

- `db:migrate` — run all pending migrations
- `db:rollback` — roll back applied migrations
- `db:status` — display discovered and recorded migration status

For invocation details, supported options, and examples, see [Console Commands](../console/commands.md).

### Planning and status

Use the planning methods to inspect exactly which migrations `migrate()` or `rollback()` would execute:

```php
$pending = $runner->getPendingMigrations();
$rollback = $runner->getRollbackMigrations(2);
$rollbackSteps = $runner->getRollbackMigrations(null, 3);
```

Both methods return migrations as `migration name => migration class`, in execution order. If a selected rollback migration is recorded but its implementation cannot be discovered, `getRollbackMigrations()` throws a `DbException`.

Use `getStatus()` to inspect all discovered and recorded migrations:

```php
$status = $runner->getStatus();
```

Each status row contains `migration`, `status`, and `batch`. Rows are naturally sorted by migration name and use the following statuses:

- `up` — discovered and recorded
- `down` — discovered but not recorded; `batch` is `null`
- `missing` — recorded but no longer discovered

### Dry runs

Use `--dry-run` to display the ordered migration plan without executing it:

```bash
app db:migrate --dry-run
app db:rollback --dry-run
```

Dry-run output lists each migration name with its intended `up` or `down` action. It does not instantiate migrations, call migration methods, or change migration history. It does not display SQL because migrations can execute arbitrary PHP, inspect current database state, and execute queries without using Forge.

### Migrate

`MigrationRunner::migrate()` executes the plan returned by `getPendingMigrations()`. For each migration, `up()` is called when present and the migration name is recorded into history as part of a new batch.

To target a specific connection, call `setConnection()` before running (for example `$runner->setConnection(db('reporting'));`).

```php
$runner->migrate();
```

### Rollback

`MigrationRunner::rollback()` executes the plan returned by `getRollbackMigrations()` based on recorded history (latest first). For each matched migration class, `down()` is called when present, and the migration is removed from history.

To target a specific connection, call `setConnection()` before rolling back.

If you provide both `$batches` and `$steps`, rollback stops when either limit is reached.

If a migration recorded in history can no longer be discovered, rollback throws a `DbException` and leaves that history entry intact.

```php
// Roll back the latest batch (default behavior).
$runner->rollback();

// Roll back the latest 2 batches.
$runner->rollback(2);

// Roll back the latest 3 applied migrations, regardless of batches.
$runner->rollback(null, 3);
```

## Migration history

`MigrationHistory` stores applied migrations per connection in a `migrations` table. Construction and reads do not create the table. When no history table exists, `all()` returns an empty array and `getNextBatch()` returns `1`. The table structure is checked and created when `add()` first records a migration.

The history table includes `id`, `batch`, `migration`, and `timestamp` columns.

History behavior used by `MigrationRunner`:

- `MigrationHistory::all()` returns applied migrations ordered by batch and most-recent first
- `MigrationHistory::getNextBatch()` determines the next batch number for a migrate run
- `MigrationHistory::add()` and `MigrationHistory::delete()` record and remove entries

## Behavior notes

A few behaviors are worth keeping in mind:

- `migrate()` skips any migration name already present in history.
- Migration plans use the same selection logic as execution.
- Migration status and dry-run planning do not create the migration history table.
- `rollback()` throws and preserves the history entry when the corresponding migration class cannot be found.
- Migration execution is not automatically wrapped in a transaction.
- If a migration does not implement `up()` or `down()`, the missing method is skipped and execution continues.

## Related

- [Database connections](connections.md)
- [Forge](forge.md)
- [Database types](types.md)
- [Schema](schema.md)
