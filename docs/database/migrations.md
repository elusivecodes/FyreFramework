# Database Migrations

Migrations provide a repeatable way to evolve database structure over time by applying ordered schema changes and tracking what has run on a connection.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Where migrations fit](#where-migrations-fit)
- [Writing migrations](#writing-migrations)
- [Migration discovery](#migration-discovery)
  - [Naming rules](#naming-rules)
  - [Discovery and ordering](#discovery-and-ordering)
- [Running migrations](#running-migrations)
  - [Via console commands](#via-console-commands)
  - [Getting a MigrationRunner instance](#getting-a-migrationrunner-instance)
  - [Migrate](#migrate)
  - [Rollback](#rollback)
- [Migration history](#migration-history)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use migrations when you want database schema changes to be repeatable, ordered, and tracked per connection.

Each connection maintains its own migration history in the `migrations` table, so it’s safe to run `migrate()` repeatedly without reapplying the same migration.

## Quick start

In a typical app:

1. Write migrations as `Migration_*` classes (see [Writing migrations](#writing-migrations)).
2. Register your migration namespace(s) on a `MigrationRunner`.
3. Run `migrate()` (or run the `db:migrate` console command).

Minimal example running migrations from code:

```php
use Fyre\DB\Migration\MigrationRunner;

$runner = app(MigrationRunner::class);
$runner->addNamespace('App\Migrations');

$runner->migrate();
```

⚠️ Migration execution is not automatically wrapped in a transaction. If you need all-or-nothing behavior, wrap your changes in a transaction where possible, or design migrations to be safe to rerun after a partial failure.

Example: wrapping a migrate run in a transaction (when your driver supports transactional DDL)

```php
$db = db();
$runner->setConnection($db);

$db->transactional(static fn() => $runner->migrate());
```

## Where migrations fit

Migrations sit on top of [Forge](forge.md): migrations define schema changes, and Forge executes the DDL for the current connection driver.

The migration system centers on three classes:

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

Use `MigrationRunner::addNamespace()` to register namespaces to scan (see [Getting a MigrationRunner instance](#getting-a-migrationrunner-instance)).

### Discovery and ordering

Discovery behavior:

- each configured namespace is searched for files matching `Migration_*.php`
- each discovered class is checked to be a subclass of `Migration` and non-abstract
- migrations are sorted by migration name using natural sorting before execution

## Running migrations

📌 `MigrationRunner` applies migrations in order and records execution in `MigrationHistory`. Execution is not automatically wrapped in a transaction.

### Via console commands

In most apps, migrations are run through the console subsystem rather than directly calling `MigrationRunner` methods.

Use the built-in database migration commands:

- `db:migrate` — run all pending migrations
- `db:rollback` — roll back applied migrations

For invocation details, supported options, and examples, see [Built-in Console Commands](../console/commands.md).

### Getting a MigrationRunner instance

Most examples on this page assume you already have a `$runner` (`MigrationRunner`) instance (see [Quick start](#quick-start)).

```php
$runner->addNamespace('App\Migrations');

// Optional: target a specific connection.
$runner->setConnection(db('reporting'));
```

### Migrate

`MigrationRunner::migrate()` runs all discovered migrations that are not already present in history. For each migration, `up()` is called when present and the migration name is recorded into history as part of a new batch.

```php
$runner->migrate();
```

### Rollback

`MigrationRunner::rollback()` rolls back previously applied migrations based on recorded history (latest first). For each matched migration class, `down()` is called when present, and the migration is removed from history.

```php
// Roll back the latest batch (default behavior).
$runner->rollback();

// Roll back the latest 2 batches.
$runner->rollback(2);

// Roll back the latest 3 applied migrations, regardless of batches.
$runner->rollback(null, 3);
```

## Migration history

`MigrationHistory` stores applied migrations per connection in a `migrations` table. It also ensures that the table exists with the expected columns and indexes when it is constructed.

The history table includes `id`, `batch`, `migration`, and `timestamp` columns.

History behavior used by `MigrationRunner`:

- `MigrationHistory::all()` returns applied migrations ordered by batch and most-recent first
- `MigrationHistory::getNextBatch()` determines the next batch number for a migrate run
- `MigrationHistory::add()` and `MigrationHistory::delete()` record and remove entries

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `migrate()` skips any migration name already present in history.
- `rollback()` removes history entries even when the corresponding migration class is not discoverable (in that case, no `down()` can be executed).
- `MigrationRunner::clear()` resets loaded migrations and cached state; use it when migration files or discovery configuration change.
- Migration execution records history even when a migration does not implement `up()`/`down()`; missing methods are skipped and execution continues.

## Related

- [Database connections](connections.md)
- [Forge](forge.md)
- [Database types](types.md)
- [Schema](schema.md)
