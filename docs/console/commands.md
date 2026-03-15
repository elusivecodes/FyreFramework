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

If no alias is provided, `handle()` prints the available command list.

Command input follows a few simple rules:

- `--name value` and `--name=value` are both supported
- options without a value are treated as `true`
- `-o value` is supported when the option key is a single character
- positional values are matched to option keys in the order the command defines them

For example, `db:rollback default 2 --steps 5` resolves as `db = default`, `batches = 2`, `steps = 5`.

## Built-in commands

### Database commands

#### `db:migrate`

Runs all pending migrations.

Options:

- `db` (`string`): connection key (default: `ConnectionManager::DEFAULT`)

Examples:

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

Examples:

```php
$commandRunner->handle(['app', 'db:rollback']);
$commandRunner->handle(['app', 'db:rollback', 'default', '2', '--steps', '5']);
```

### Queue commands

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

`make:*` commands generate common application files from framework stubs. They create missing directories, but they do not overwrite existing files.

Namespace-based generators write to the first matching configured namespace path. Template-style generators write beneath the resolved template path and support dot notation such as `admin.posts.index`.

Common generators:

- `make:command` - generate a console command class
- `make:config` - generate a config file
- `make:controller` - generate a controller class
- `make:entity` - generate an entity class
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

Examples:

```php
$commandRunner->handle(['app', 'make:controller', 'Posts']);
$commandRunner->handle(['app', 'make:migration', 'CreatePosts']);
$commandRunner->handle(['app', 'make:template', 'admin.posts.index']);
```

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
    public function __construct(
        Console $io,
        protected CacheManager $cacheManager,
    ) {
        parent::__construct($io);
    }

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
- `$arguments` (`array<string|true>`): parsed option and argument values.

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

- long options support both `--db default` and `--db=default`
- positional arguments follow the option order defined by the command
- `clear()` also removes registered namespaces, so add them again before rediscovering commands
- `queue:worker` requires process forking (`ext-pcntl`)
- `make:*` commands do not overwrite existing files

## Related

- [Console](index.md)
- [Console I/O](console.md)
- [Database Migrations](../database/migrations.md)
- [Queue Worker](../queue/worker.md)
