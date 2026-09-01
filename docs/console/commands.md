# Console Commands

Use console commands to run framework tasks such as migrations and queue workers, scaffold files with `make:*`, and add your own CLI tasks.

## Table of Contents

- [Start here](#start-here)
- [Running commands](#running-commands)
- [Built-in commands](#built-in-commands)
  - [Database commands](#database-commands)
  - [Queue commands](#queue-commands)
  - [Make commands](#make-commands)
- [Writing custom commands](#writing-custom-commands)
  - [Creating a command](#creating-a-command)
  - [Defining options](#defining-options)
  - [Implementing `run()`](#implementing-run)
- [Method guide](#method-guide)
  - [`CommandRunner`](#commandrunner)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Pick a path based on what you are doing:

- **Running built-in commands**: start with [Running commands](#running-commands)
- **Working with migrations**: see [Database commands](#database-commands)
- **Working with queues**: see [Queue commands](#queue-commands)
- **Generating classes and files**: see [Make commands](#make-commands)
- **Adding your own CLI task**: start with [Creating a command](#creating-a-command)

## Running commands

Most examples on this page assume you already have a `$commandRunner` instance.

When you already have a CLI `argv` array, use `CommandRunner::handle()`:

```php
$exitCode = $commandRunner->handle($_SERVER['argv']);
```

The first `argv` value is the script name. If you build an `argv` array manually, the first value can be any placeholder.

When you want to run a command directly by alias, use `CommandRunner::run()`:

```php
$exitCode = $commandRunner->run('db:migrate', [
    'db' => 'default',
]);
```

If no alias is provided, `handle()` displays usage, global options, and the available command list. The same help is available with `--help` or `-h`:

```bash
app
app --help
app -h
```

Pass `--help` or `-h` after a command alias to display its description, usage, and option metadata without validating arguments or prompting for input:

```bash
app db:migrate --help
```

Display the installed framework version with `--version` or `-V`:

```bash
app --version
app -V
```

The global `help` and `version` options, including their `h` and `V` short forms, are reserved and should not be used as custom command option keys.

Command input follows a few simple rules:

- `--name value` and `--name=value` are both supported
- options without a value are treated as `true`
- `-o value` is supported when the option key is a single character
- unknown named options produce an error
- positional values are matched to option keys in the order the command defines them

For example, `db:rollback default 2 --steps 5` resolves as `db = default`, `batches = 2`, `steps = 5`.

## Built-in commands

### Database commands

#### `db:lock:purge`

Removes expired database lock rows. Expired rows are not removed automatically, but they do not prevent their lock names from being acquired again. Run this command manually or on a schedule for housekeeping. If lock storage has not been initialized, the command exits successfully without creating it.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)

Examples:

```bash
app db:lock:purge --db=default
```

```php
$commandRunner->handle(['app', 'db:lock:purge']);
$commandRunner->handle(['app', 'db:lock:purge', '--db', 'default']);
```

#### `db:lock:setup`

Initializes database lock storage. Run this once for each connection that uses `Connection::lock()` outside the migration runner.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)

Examples:

```bash
app db:lock:setup --db=default
```

```php
$commandRunner->handle(['app', 'db:lock:setup']);
$commandRunner->handle(['app', 'db:lock:setup', '--db', 'default']);
```

#### `db:migrate`

Runs all pending migrations.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)
- `lockExpires` (`int`): migration lock lifetime in seconds (default: `300`)
- `dryRun` (`bool`): display the ordered `up` plan without executing migrations (default: `false`)

Examples:

```bash
app db:migrate --dry-run
app db:migrate --lock-expires=600
```

```php
$commandRunner->handle(['app', 'db:migrate']);
$commandRunner->handle(['app', 'db:migrate', '--db', 'default']);
```

```php
$exitCode = $commandRunner->run('db:migrate', [
    'db' => 'default',
]);
```

See [Database Migrations](../database/migrations.md) for the migration workflow itself.

#### `db:rollback`

Rolls back previously applied migrations.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)
- `batches` (`int|null`): number of batches to roll back (default: `1`)
- `steps` (`int|null`): number of migrations to roll back (default: `null`)
- `lockExpires` (`int`): migration lock lifetime in seconds (default: `300`)
- `dryRun` (`bool`): display the ordered `down` plan without executing migrations (default: `false`)

Examples:

```bash
app db:rollback --dry-run
app db:rollback --lock-expires=600
```

```php
$commandRunner->handle(['app', 'db:rollback']);
$commandRunner->handle(['app', 'db:rollback', 'default', '2', '--steps', '5']);
```

#### `db:status`

Displays every discovered or recorded migration with its status and batch. A status of `up` means the migration is discovered and recorded, `down` means it is discovered but not recorded, and `missing` means it is recorded but its implementation cannot be discovered.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)

Examples:

```bash
app db:status --db=default
```

```php
$commandRunner->handle(['app', 'db:status']);
$commandRunner->handle(['app', 'db:status', '--db', 'default']);
```

### Queue commands

#### `queue:failed`

Displays retained terminal failures for a queue, ordered from newest to oldest. Failure timestamps are displayed in UTC.

Options:

- `config` (`string`): queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`): queue name (default: `Queue::DEFAULT`)
- `class` (`string|null`): limit output to one job class

Examples:

```bash
app queue:failed --config=default --queue=default
```

```php
$commandRunner->handle(['app', 'queue:failed']);
$commandRunner->handle(['app', 'queue:failed', '--queue', 'emails', '--class', 'App\Jobs\SendEmailJob']);
```

#### `queue:purge`

Removes retained failures without retrying them. If IDs are omitted, all failures matching the queue and optional class filter are selected. The command requests confirmation whenever at least one failure matches.

Options:

- `ids` (`string|null`): comma-separated failed job IDs; may be supplied as the first positional value
- `config` (`string`): queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`): queue name (default: `Queue::DEFAULT`)
- `class` (`string|null`): limit removal to one job class
- `force` (`bool`): skip confirmation (default: `false`)

Examples:

```bash
app queue:purge 0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789 --queue=emails
app queue:purge --ids=0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789 --queue=emails
app queue:purge --queue=emails --class='App\Jobs\SendEmailJob'
app queue:purge --queue=emails --force
```

```php
$commandRunner->handle([
    'app',
    'queue:purge',
    '0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789',
    '--queue',
    'emails',
]);
```

#### `queue:retry`

Requeues retained failed jobs and removes each retained record when enqueueing succeeds. If IDs are omitted, all failures matching the queue and optional class filter are selected. The command requests confirmation whenever at least one failure matches.

Options:

- `ids` (`string|null`): comma-separated failed job IDs; may be supplied as the first positional value
- `config` (`string`): queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`): queue name (default: `Queue::DEFAULT`)
- `class` (`string|null`): limit retries to one job class
- `force` (`bool`): skip confirmation (default: `false`)

Examples:

```bash
app queue:retry 0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789 --queue=emails
app queue:retry --ids=0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789 --queue=emails
app queue:retry --queue=emails --class='App\Jobs\SendEmailJob'
app queue:retry --queue=emails --force
```

```php
$commandRunner->handle([
    'app',
    'queue:retry',
    '0123456789abcdef0123456789abcdef,abcdef0123456789abcdef0123456789',
    '--queue',
    'emails',
]);
```

#### `queue:worker`

Starts a queue worker.

Options:

- `config` (`string`): queue handler config key (default: `QueueManager::DEFAULT`)
- `queue` (`string`): queue name to poll (default: `Queue::DEFAULT`)
- `maxJobs` (`int`): maximum number of jobs to process before stopping (default: `0`)
- `maxRuntime` (`int`): maximum runtime in seconds before stopping (default: `0`)

Examples:

```php
$commandRunner->handle(['app', 'queue:worker']);
$commandRunner->handle(['app', 'queue:worker', '--max-runtime', '60']);
```

See [Queue Worker](../queue/worker.md) for worker behavior and lifecycle details.

#### `queue:stats`

Displays queue stats.

Options:

- `config` (`string|null`): limit output to one queue handler config key
- `queue` (`string|null`): limit output to one queue name

Examples:

```php
$commandRunner->handle(['app', 'queue:stats']);
$commandRunner->handle(['app', 'queue:stats', '--config', 'default']);
```

### Make commands

`make:*` commands generate common application files from framework stubs. They create missing directories and, by default, do not overwrite existing files. Pass `--force` to overwrite an existing target.

Successful `make:*` commands print the path of each generated file. `make:model` prints paths after all files have been written.

Namespace-based generators write to the first matching configured namespace path. Template-style generators write beneath the resolved template path and support dot notation such as `admin.posts.index`.

Common generators:

- `make:command` - generate a console command class
- `make:config` - generate a config file
- `make:controller` - generate a controller class
- `make:entity` - generate an entity class
- `make:enum` - generate an enum class
- `make:fixture` - generate a fixture class; pass `--data` to include existing model data (`--limit` defaults to `10`)
- `make:form` - generate a form class
- `make:helper` - generate a helper class
- `make:job` - generate a job class
- `make:lang` - generate a language file
- `make:layout` - generate a layout template
- `make:middleware` - generate a middleware class
- `make:migration` - generate a migration class
- `make:model` - generate a model class
- `make:policy` - generate a policy class
- `make:cell` - generate a cell class
- `make:cell_template` - generate a cell template
- `make:element` - generate an element template
- `make:template` - generate a template
- `make:test` - generate a test case

Examples:

```php
$commandRunner->handle(['app', 'make:controller', 'Posts']);
$commandRunner->handle(['app', 'make:enum', 'Status', '--cases=Draft:draft,Published:published']);
$commandRunner->handle(['app', 'make:enum', 'State', '--cases=Draft,Published']);
$commandRunner->handle(['app', 'make:fixture', 'Items', '--data', '--limit=25']);
$commandRunner->handle(['app', 'make:migration', 'CreatePosts']);
$commandRunner->handle(['app', 'make:template', 'admin.posts.index']);
```

`make:enum` defaults to `App\Enums`. The `--cases` option accepts comma-separated case names for unit enums, or `Case:value` pairs for string-backed enums.

## Writing custom commands

### Creating a command

If you are using the usual application setup, place commands in `App\Commands`. If you want to load commands from another namespace, register it with `addNamespace()`:

```php
$commandRunner->addNamespace('Plugin\Commands');
```

Create a command class that:

- extends `Fyre\Console\Command`
- ends with `Command`
- defines an alias, description, and options
- implements `run()`

If `$alias` is `null`, the alias is generated from the class name.

Example:

```php
namespace App\Commands;

use Fyre\Cache\CacheManager;
use Fyre\Console\Command;
use Fyre\Console\Console;

class ClearCacheCommand extends Command
{
    protected string|null $alias = 'app:clear-cache';

    protected string $description = 'Delete a cache key.';

    protected array $options = [
        'cache' => [
            'text' => 'Cache config key',
            'default' => 'default',
        ],
        'key' => [
            'text' => 'Cache key to delete',
            'required' => true,
        ],
        'force' => [
            'text' => 'Skip confirmation',
            'as' => 'boolean',
            'default' => false,
        ],
    ];

    public function __construct(
        Console $io,
        protected CacheManager $cacheManager,
    ) {
        parent::__construct($io);
    }

    public function run(string $cache, string $key, bool $force): int
    {
        if (!$force && !$this->io->confirm('Delete cache key "'.$key.'"?', false)) {
            return self::CODE_SUCCESS;
        }

        $this->cacheManager->use($cache)->delete($key);
        $this->io->success('Deleted: '.$key);

        return self::CODE_SUCCESS;
    }
}
```

Run it by alias:

```php
$exitCode = $commandRunner->run('app:clear-cache', [
    'cache' => 'default',
    'key' => 'posts.42',
]);
```

### Defining options

Command options come from the command's `$options` property. Each option key maps to either:

- a string, used as prompt text for a required value
- an array of option metadata

Supported metadata keys:

- `text` (`string`) - prompt text
- `required` (`bool`) - whether a value must be provided
- `values` (`array|null`) - allowed values
- `as` (`string`) - parse type (defaults to `string`)
- `default` (`mixed`) - default value when omitted

### Implementing `run()`

`run()` should accept the input values your command needs.

- parameter names that match option keys receive parsed option values
- constructor injection is a good place for shared services and `$this->io`
- `run()` parameters are a good place for per-invocation command input

If you add new namespaces after commands have already been discovered, call `clear()`, re-add the namespaces you want, and run discovery again.

## Method guide

This section focuses on the `CommandRunner` methods you are most likely to call directly.

### `CommandRunner`

#### **Run a command from `argv`** (`handle()`)

Parse a CLI-style `argv` array, run the resolved command, and return the exit code.

Arguments:
- `$argv` (`string[]`): the CLI arguments, including the script name as the first item.

```php
$exitCode = $commandRunner->handle($_SERVER['argv']);
```

#### **Run a command by alias** (`run()`)

Run a command directly by alias.

Arguments:
- `$alias` (`string`): the command alias.
- `$arguments` (`array<array-key, mixed>`): positional or named option values.

```php
$exitCode = $commandRunner->run('db:migrate', [
    'db' => 'default',
]);
```

#### **Add a command namespace** (`addNamespace()`)

Register a namespace used for command discovery.

Arguments:
- `$namespace` (`string`): the namespace to add.

```php
$commandRunner->addNamespace('Plugin\Commands');
```

#### **Reset discovered commands** (`clear()`)

Reset command discovery state.

```php
$commandRunner->clear();
$commandRunner->addNamespace('App\Commands');
```

## Behavior notes

A few practical details are worth keeping in mind:

- Long options support both `--db default` and `--db=default`.
- Global and command help bypass argument validation and interactive prompts.
- Unknown named options return an error instead of being treated as positional values.
- Positional arguments follow the option order defined by the command.
- `clear()` also removes registered namespaces, so add them again before rediscovering commands.
- `queue:worker` runs in the foreground and requires `ext-pcntl` for signal handling.
- `make:*` commands do not overwrite existing files unless you pass `--force`.

## Related

- [Console](index.md)
- [Console I/O](console.md)
- [Database Migrations](../database/migrations.md)
- [Queue Worker](../queue/worker.md)
