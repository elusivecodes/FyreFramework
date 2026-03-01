# Built-in Console Commands

The framework ships a small set of ready-to-use console commands for database migrations, queue operations, and common scaffolding tasks.

## Table of Contents

- [Purpose](#purpose)
- [Where Commands Fit](#where-commands-fit)
- [Running Commands](#running-commands)
- [Database Commands](#database-commands)
  - [`db:migrate`](#dbmigrate)
  - [`db:rollback`](#dbrollback)
- [Queue Commands](#queue-commands)
  - [`queue:worker`](#queueworker)
  - [`queue:stats`](#queuestats)
- [Make Commands](#make-commands)
  - [`make:command`](#makecommand)
  - [`make:config`](#makeconfig)
  - [`make:controller`](#makecontroller)
  - [`make:entity`](#makeentity)
  - [`make:helper`](#makehelper)
  - [`make:job`](#makejob)
  - [`make:lang`](#makelang)
  - [`make:layout`](#makelayout)
  - [`make:middleware`](#makemiddleware)
  - [`make:migration`](#makemigration)
  - [`make:model`](#makemodel)
  - [`make:policy`](#makepolicy)
  - [`make:cell`](#makecell)
  - [`make:cell_template`](#makecell_template)
  - [`make:element`](#makeelement)
  - [`make:template`](#maketemplate)
- [Custom Commands](#custom-commands)
  - [Creating a command](#creating-a-command)
  - [Defining options](#defining-options)
  - [Implementing `run()`](#implementing-run)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use these commands when you want consistent CLI behavior (argument parsing, prompting for missing required options, and dependency injection) without writing your own tooling.

## Where Commands Fit

Built-in commands live under `Fyre\Commands` and are intended to be discovered and executed through the framework’s console runtime (`CommandRunner`).

`CommandRunner` discovers commands by scanning its registered namespaces for `*Command.php` files, then reflecting each command’s default `alias`, `description`, and `options` values. The command list is cached after it is first built.

At runtime, each command’s `run()` method is invoked through the container, which means `run()` parameters can include both services (injected) and option values (parsed from `argv`).

## Running Commands

Commands are invoked by alias (for example, `db:migrate`), followed by any options.

Most examples on this page assume you already have a `$commandRunner` instance (for example via dependency injection).

When using the console runtime directly, `CommandRunner::handle()` accepts a normal PHP `argv` array (script name first), parses the alias and options, and returns the command’s exit code:

```php
$exitCode = $commandRunner->handle($_SERVER['argv']);
```

The first `argv` value is the script name. If you construct `argv` arrays manually (as in the examples below), the first value can be any placeholder.

When no alias is provided, `handle()` prints a table of available commands (alias, description, and option keys).

You can also execute a command directly (bypassing `argv` parsing) with `CommandRunner::run()`:

```php
$exitCode = $commandRunner->run('db:migrate', [
    'db' => 'default',
]);
```

Exit codes follow `Fyre\Console\Command`:

- `Command::CODE_SUCCESS` (`0`)
- `Command::CODE_ERROR` (`1`)

Argument parsing rules used by the console runtime:

- Options are read from `--option value` or `-o value`.
- Options without a value are treated as `true`.
- For boolean options, values `false`, `n`, and `no` (and also `0`) are treated as `false`; other non-empty values are treated as `true`.
- Option names are normalized to lower camelCase (for example, `--max-runtime` becomes `maxRuntime`).
- Non-option arguments are treated as positional values and can be used instead of named options.

`CommandRunner` resolves option values in two passes:

- Named values are applied first (for example, `--steps 5` becomes `['steps' => '5']`).
- Any remaining positional values are assigned to option keys in the order the command defines them.

For example, given `db:rollback`’s option order (`db`, `batches`, `steps`), the `argv` equivalent of `db:rollback default 2 --steps 5` resolves as `db = default`, `batches = 2`, `steps = 5`.

## Database Commands

### `db:migrate`

Runs all pending migrations using the configured migration namespaces.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)

This command resolves the requested connection and forwards it into `MigrationRunner`, then calls `MigrationRunner::migrate()`.

For migration discovery, ordering, and rollback behavior, see [Database Migrations](../database/migrations.md).

Run it via `argv` parsing:

```php
$commandRunner->handle(['app', 'db:migrate']);
$commandRunner->handle(['app', 'db:migrate', '--db', 'default']);
```

Programmatic invocation:

```php
use Fyre\DB\ConnectionManager;

$exitCode = $commandRunner->run('db:migrate', [
    'db' => ConnectionManager::DEFAULT,
]);
```

### `db:rollback`

Rolls back previously applied migrations.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)
- `batches` (`int|null`): number of batches to roll back (default: `1`)
- `steps` (`int|null`): number of migrations to roll back (default: `null`)

This command resolves the requested connection and forwards it into `MigrationRunner`, then calls `MigrationRunner::rollback($batches, $steps)`.

For migration discovery, ordering, and rollback behavior, see [Database Migrations](../database/migrations.md).

Run it via `argv` parsing:

```php
$commandRunner->handle(['app', 'db:rollback']);
$commandRunner->handle(['app', 'db:rollback', 'default', '2', '--steps', '5']);
```

## Queue Commands

### `queue:worker`

Starts a background queue worker process.

Options:

- `config` (`string`): queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`): queue name to poll (default: `Queue::DEFAULT`)
- `maxJobs` (`int`): maximum number of jobs to process before stopping (default: `0`, unlimited)
- `maxRuntime` (`int`): maximum runtime in seconds before stopping (default: `0`, unlimited)

This command forks a new process and runs `Worker::run()` in the child process. The parent process prints the PID and exits immediately.

For the worker loop, job execution rules, and lifecycle events, see [Queue Worker](../queue/worker.md).

Run it via `argv` parsing:

```php
$commandRunner->handle(['app', 'queue:worker']);
$commandRunner->handle(['app', 'queue:worker', '--max-runtime', '60']);
```

### `queue:stats`

Displays per-queue stats for configured queue handlers.

Options:

- `config` (`string|null`): filter output to a single queue handler config key (default: `null`)
- `queue` (`string|null`): filter output to a single queue name (default: `null`)

When no filters are provided, stats are displayed for all configured queue handlers and each handler’s active queues.

Run it via `argv` parsing:

```php
$commandRunner->handle(['app', 'queue:stats']);
$commandRunner->handle(['app', 'queue:stats', '--config', 'default']);
```

## Make Commands

`make:*` commands generate new PHP classes and template/config files from stubs shipped with the framework. They create missing directories, but do not overwrite existing files.

Namespace-based generators resolve a filesystem output path from the provided namespace. If the namespace cannot be resolved, the command fails with `Namespace path not found.`

Template-style generators accept a base `path` and write into a subfolder based on the type (layouts, elements, cells). The `template`/`file` value supports dot notation via `Make::normalizePath()` (for example, `admin.users/index` and `admin.users.index` both target nested folders).

Run generators via `argv` parsing:

```php
$commandRunner->handle(['app', 'make:controller', 'Posts']);
$commandRunner->handle(['app', 'make:migration', 'CreatePosts']);
$commandRunner->handle(['app', 'make:template', 'admin.posts.index']);
```

### `make:command`

Generates a console command class using the `command` stub.

Options:

- `name` (`string`): command class name (required)
- `alias` (`string|null`): command alias (default: derived from the class name)
- `description` (`string|null`): description text (default: `''`)
- `namespace` (`string|null`): target namespace (default: first registered command namespace, or `App\Commands`)

When `alias` is omitted, it is generated from the class name (class short name without `Command`, converted to `snake_case` and lowercased).

### `make:config`

Generates a config file using the `config` stub.

Options:

- `file` (`string`): config file name (required)
- `path` (`string|null`): base config path (default: first configured config path)

### `make:controller`

Generates a controller class using the `controller` stub.

Options:

- `name` (`string`): controller name (required)
- `namespace` (`string|null`): target namespace (default: `App\Controllers`)

The generated class name is suffixed with `Controller`.

### `make:entity`

Generates an entity class using the `entity` stub.

Options:

- `name` (`string`): entity name (required)
- `namespace` (`string|null`): target namespace (default: first registered entity namespace, or `App\Entities`)

### `make:helper`

Generates a helper class using the `helper` stub.

Options:

- `name` (`string`): helper name (required)
- `namespace` (`string|null`): target namespace (default: first registered helper namespace, or `App\Helpers`)

The generated class name is suffixed with `Helper`.

### `make:job`

Generates a job class using the `job` stub.

Options:

- `name` (`string`): job name (required)
- `namespace` (`string|null`): target namespace (default: `App\Jobs`)

The generated class name is suffixed with `Job`.

### `make:lang`

Generates a language file using the `lang` stub.

Options:

- `file` (`string`): language file name (required)
- `language` (`string|null`): locale folder name (default: `Lang` default locale)
- `path` (`string|null`): base lang path (default: first configured lang path)

### `make:layout`

Generates a layout template file using the `layout` stub.

Options:

- `template` (`string`): layout template name (required)
- `path` (`string|null`): base template path (default: first configured template path)

The output is written beneath the resolved template path in the `layouts` folder.

### `make:middleware`

Generates a middleware class using the `middleware` stub.

Options:

- `name` (`string`): middleware name (required)
- `namespace` (`string|null`): target namespace (default: `App\Middleware`)

The generated class name is suffixed with `Middleware`.

### `make:migration`

Generates a migration class using the `migration` stub.

Options:

- `name` (`string`): migration name (required)
- `version` (`string|null`): version prefix (default: current timestamp formatted as `YmdHis`)
- `namespace` (`string|null`): target namespace (default: first registered migration namespace, or `App\Migrations`)

The generated class name is `Migration_{version}_{name}`.

### `make:model`

Generates a model class using the `model` stub.

Options:

- `name` (`string`): model name (required)
- `namespace` (`string|null`): target namespace (default: first registered model namespace, or `App\Models`)

The generated class name is suffixed with `Model`.

### `make:policy`

Generates a policy class using the `policy` stub.

Options:

- `name` (`string`): policy name (required)
- `namespace` (`string|null`): target namespace (default: first registered policy namespace, or `App\Policies`)

The generated class name is suffixed with `Policy`.

### `make:cell`

Generates a cell class using the `cell` stub.

Options:

- `name` (`string`): cell name (required)
- `method` (`string`): generated method name (default: `display`)
- `namespace` (`string|null`): target namespace (default: first registered cell namespace, or `App\Cells`)

The generated class name is suffixed with `Cell`.

### `make:cell_template`

Generates a cell template file using the `cell_template` stub.

Options:

- `template` (`string`): cell template name (required)
- `path` (`string|null`): base template path (default: first configured template path)

The output is written beneath the resolved template path in the `cells` folder.

### `make:element`

Generates an element template file using the `element` stub.

Options:

- `template` (`string`): element template name (required)
- `path` (`string|null`): base template path (default: first configured template path)

The output is written beneath the resolved template path in the `elements` folder.

### `make:template`

Generates a template file using the `template` stub.

Options:

- `template` (`string`): template name (required)
- `path` (`string|null`): base template path (default: first configured template path)

The output is written beneath the resolved template path.

## Custom Commands

### Creating a command

To add your own commands, create a concrete class that:

- extends `Fyre\Console\Command`
- ends with `Command` (for example, `ClearCacheCommand`)
- lives in a namespace registered on `CommandRunner` via `addNamespace()`

By default, the command alias is taken from the command’s `$alias` property. If `$alias` is `null`, it is derived from the class name (short name without `Command`, converted to `snake_case`).

### Defining options

Command option definitions come from the command’s `$options` property. Each option key maps to either:

- a string (used as prompt text when the option is required), or
- an array of option metadata

Supported metadata keys:

- `text` (`string`): prompt text
- `required` (`bool`): whether a value is required (missing values are prompted for)
- `values` (`array|null`): list/map of allowed values (when `required` is `true`, missing/invalid values prompt a choice list)
- `as` (`string`): parse type (defaults to `string`)
- `default` (`mixed`): default value when omitted

### Implementing `run()`

`CommandRunner` executes the command’s `run()` method through the container:

- parameters matching option keys receive parsed option values
- other parameters are resolved as services by the container

If you change registered namespaces or add new command classes after the command list is built, create a fresh `CommandRunner` instance (or call `clear()` and re-register namespaces) to force rediscovery.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Use space-separated options like `--db default`; `--db=default` is treated as a different option name.
- `-o` is supported, but built-in commands do not define single-letter option keys, so prefer `--name`, `--file`, `--template`, and similar.
- Positional arguments are mapped to option keys in the order the command defines them; mixing positional and named values can be confusing.
- `queue:worker` requires process forking; if `pcntl_fork()` is unavailable, the worker cannot be started this way.
- `make:*` commands will not overwrite files and will fail when the target path already exists.

## Related

- [Console](index.md)
- [Console I/O](console.md)
- [Database Migrations](../database/migrations.md)
- [Queue Worker](../queue/worker.md)
