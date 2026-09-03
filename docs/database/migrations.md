# Database migrations

Use migrations to apply versioned database changes in a predictable order and roll them back when necessary.

Migration classes describe the change with [Forge](forge.md). `MigrationRunner` discovers the classes, plans execution, coordinates locking, and records completed migrations.

## Table of Contents

- [Write a migration](#write-a-migration)
- [Discover migrations](#discover-migrations)
- [Inspect plans and status](#inspect-plans-and-status)
  - [Pending migrations](#pending-migrations)
  - [Rollback migrations](#rollback-migrations)
  - [Migration status](#migration-status)
- [Preview with a dry run](#preview-with-a-dry-run)
- [Apply migrations](#apply-migrations)
- [Roll back migrations](#roll-back-migrations)
- [Migration locking](#migration-locking)
- [Database commands](#database-commands)
- [Migration history](#migration-history)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Write a migration

Create a class whose name and file both begin with `Migration_`, extend `Migration`, and implement `up()` and, where possible, `down()`:

```php
use Fyre\DB\Migration\Migration;

class Migration_20240201_CreateRoles extends Migration
{
    public function up(): void
    {
        $this->forge->createTable(
            'roles',
            [
                'name' => [
                    'length' => 100,
                ],
            ],
            [
                'name' => [
                    'unique' => true,
                ],
            ]
        );
    }

    public function down(): void
    {
        $this->forge->dropTable('roles');
    }
}
```

The runner injects the current `Forge` as `$this->forge`. See [Forge](forge.md) for columns, indexes, constraints, and driver limitations.

Migration execution is not automatically transactional. If the driver supports transactional DDL and a change must commit as one unit, manage the transaction inside the migration:

```php
public function up(): void
{
    $this->forge->getConnection()->transactional(function(): void {
        // Apply related changes.
    });
}
```

Design migrations so a partial failure can be diagnosed and recovered. If `up()` or `down()` is absent, the runner skips that method but still updates migration history for the selected direction.

## Discover migrations

The default `MigrationRunner` scans `App\Migrations`. Register additional namespaces with `addNamespace()` when migrations live elsewhere:

```php
$runner->addNamespace('Plugin\Migrations');
```

A discovered migration must:

- live in a file matching `Migration_*.php`
- have a short class name beginning with `Migration_`
- extend `Fyre\DB\Migration\Migration`
- be non-abstract

The migration name is the short class name after `Migration_`; `Migration_20240201_CreateRoles` is recorded as `20240201_CreateRoles`.

Migrations are naturally sorted by that name. If more than one discovered class produces the same migration name, the later discovery replaces the earlier one.

## Inspect plans and status

Planning methods return data without instantiating migrations, changing history, creating framework tables, or acquiring the migration lock.

### Pending migrations

`getPendingMigrations()` returns discovered migrations that are not in history:

```php
$pending = $runner->getPendingMigrations();
```

The result is ordered as `migration name => migration class`, matching the order used by `migrate()`.

### Rollback migrations

`getRollbackMigrations($batches = 1, $steps = null)` returns applied migrations in rollback order, latest first:

```php
// Latest batch.
$rollback = $runner->getRollbackMigrations();

// Latest two batches.
$rollback = $runner->getRollbackMigrations(2);

// Latest three migrations, regardless of batch.
$rollback = $runner->getRollbackMigrations(null, 3);
```

When both limits are provided, planning stops as soon as either limit is reached. If a selected history entry no longer has a discoverable implementation, planning throws a `DbException` rather than silently removing it.

### Migration status

`getStatus()` merges discovery with recorded history and naturally sorts every row by migration name:

| Status | Meaning | Batch |
| --- | --- | --- |
| `up` | discovered and recorded | recorded batch |
| `down` | discovered but not recorded | `null` |
| `missing` | recorded but no longer discovered | recorded batch |

Use `db:status` for the same information as a console table. A missing migration remains visible without making status fail.

## Preview with a dry run

Pass `--dry-run` to `db:migrate` or `db:rollback` to print the ordered migration names and intended `up` or `down` action:

```bash
app db:migrate --dry-run
app db:rollback --dry-run
```

A dry run:

- uses the same plan as execution
- does not instantiate migration classes
- does not call `up()` or `down()`
- does not change migration history
- does not create `fyre__migrations` or `fyre__locks`
- does not acquire the migration lock

It does not claim to preview SQL. Migrations can run arbitrary PHP, inspect live database state, and execute queries without using Forge, so a general SQL preview would be incomplete.

## Apply migrations

Run all pending migrations from the console:

```bash
app db:migrate
```

Or use `MigrationRunner` directly:

```php
use Fyre\DB\Migration\MigrationRunner;

$runner = app(MigrationRunner::class);
$runner->migrate();
```

`migrate()` recomputes `getPendingMigrations()` after acquiring the lock. It calls `up()` when present and records each selected migration in a new batch. `getLastMigrationCount()` returns the number recorded by the last migrate operation.

Select another connection before planning or execution with `setConnection()`:

```php
$runner
    ->setConnection(db('reporting'))
    ->migrate();
```

## Roll back migrations

The default rollback removes the latest batch:

```bash
app db:rollback
```

From PHP, pass batch and step limits using the same rules as rollback planning:

```php
$runner->rollback();
$runner->rollback(2);
$runner->rollback(null, 3);
```

`rollback()` recomputes `getRollbackMigrations()` after acquiring the lock. It calls `down()` when present and then removes each selected migration from history. `getLastMigrationCount()` returns the number removed by the last rollback operation.

If a selected migration implementation is missing, rollback throws a `DbException` and retains that history entry.

## Migration locking

Actual migrate and rollback operations use the same database-backed lock. The execution sequence is:

1. Initialize the `fyre__migrations` and `fyre__locks` framework tables if required.
2. Acquire the migration lock without waiting.
3. Recompute the migration plan.
4. Refresh the lease before the first migration and after each migration.
5. Release the lock in a `finally` block.

If another process owns the lock, execution fails immediately with a `DbException`. If refresh fails because the lease was lost, execution also stops with a `DbException`.

The lease lasts `300` seconds by default. It must be longer than the longest individual migration because the runner only refreshes between migrations. Change it with `MigrationRunner::setLockExpires()` or `--lock-expires`:

```bash
app db:migrate --lock-expires=600
app db:rollback --lock-expires=600
```

Planning, status, and dry runs remain read-only and never acquire this lock.

Expired lock rows do not prevent acquisition, but they are not removed automatically. Run `db:lock:purge` manually or on a schedule to remove them.

## Database commands

All database commands accept `--db` and default to the `default` connection.

| Command | Options | Behavior |
| --- | --- | --- |
| `db:migrate` | `--db`, `--lock-expires=300`, `--dry-run` | apply all pending migrations or print the `up` plan |
| `db:rollback` | `--db`, `--batches=1`, `--steps`, `--lock-expires=300`, `--dry-run` | roll back history or print the `down` plan |
| `db:status` | `--db` | display migration, `up`/`down`/`missing` status, and batch |
| `db:lock:setup` | `--db` | create lock storage if it is not initialized |
| `db:lock:purge` | `--db` | delete lock rows whose expiry is at or before the database's current time |

`db:rollback` also accepts positional values in option order. For example, `app db:rollback default 2 --steps=5` uses the `default` connection and stops after two batches or five migrations, whichever is reached first.

`db:lock:setup` is only required when application code uses `Connection::lock()` independently of migrations. Migration execution initializes both framework tables itself.

`db:lock:purge` exits successfully without creating lock storage when the table is absent. When the table exists, it reports whether any expired rows were removed. Schedule it if expired-row housekeeping matters for the application.

See [Console Commands](../console/commands.md) for the general command syntax and help behavior.

## Migration history

Each connection stores applied migrations in `fyre__migrations` with `id`, `batch`, `migration`, and `timestamp` columns.

Migration history reads are safe before setup:

- `all()` returns an empty array when the table does not exist.
- `getNextBatch()` returns `1` when the table does not exist.
- `add()` ensures the table exists before recording a migration.
- `delete()` returns without changing schema when the table does not exist.
- `checkTable()` explicitly initializes migration history for execution.

`all()` orders history by batch descending and then ID descending, which is the rollback order used by the runner.

## Behavior notes

- Migration names, rather than class names, are stored in history.
- Migration plans are recalculated under the lock before execution so an earlier preview cannot become the execution source of truth.
- Migration methods and generated DDL are not automatically transactional.
- A missing `up()` or `down()` method is skipped, but the selected history change still occurs.
- A missing implementation is diagnostic in status output and an error when selected for rollback.
- The lock lease must cover one whole migration; refresh occurs between migrations, not during one.

## Related

- [Deployment](../deployment.md)
- [Database connections](connections.md)
- [Database locks](connections.md#database-locks)
- [Forge](forge.md)
- [Schema](schema.md)
- [Console Commands](../console/commands.md)
