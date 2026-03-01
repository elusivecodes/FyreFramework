# Fixtures

Fixtures provide a repeatable way to load known records into the database for tests, using framework-managed discovery and execution. For how fixtures are applied automatically in `TestCase`, see [`TestCase`](test-case.md).

## Table of Contents

- [Purpose](#purpose)
- [Discovery and registration](#discovery-and-registration)
  - [Naming conventions](#naming-conventions)
  - [Managing namespaces](#managing-namespaces)
- [Defining fixture data](#defining-fixture-data)
  - [Class alias resolution](#class-alias-resolution)
- [Loading and cleaning up data](#loading-and-cleaning-up-data)
- [Examples](#examples)
  - [Creating a fixture](#creating-a-fixture)
  - [Resolving and running a fixture](#resolving-and-running-a-fixture)
  - [Rebuilding a fixture instance](#rebuilding-a-fixture-instance)
- [Method guide](#method-guide)
  - [`FixtureRegistry`](#fixtureregistry)
  - [`Fixture`](#fixture)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 A fixture is a class that holds a dataset (rows) and knows how to insert it into the table for a model.

Fixtures are designed to be:
- easy to discover by name (an *alias*)
- easy to reuse across tests (a shared instance per alias)
- explicit about what gets written to the database

## Discovery and registration

Fixture discovery is handled by `Fyre\TestSuite\Fixture\FixtureRegistry`.

The registry resolves fixtures by:
1. Iterating over configured namespaces (in order)
2. Building a candidate class name using `{Namespace}{Alias}Fixture`
3. Accepting the first class found that is a subclass of `Fyre\TestSuite\Fixture\Fixture`
4. Building the fixture instance via the container (so constructor dependencies can be injected)

### Naming conventions

- The alias passed to the registry is the fixture name **without** the `Fixture` suffix.
- With a namespace of `App\Fixtures` and an alias of `Items`, the registry looks for `App\Fixtures\ItemsFixture`.

### Managing namespaces

Namespaces are normalized (trimmed and forced to a trailing `\`) and duplicates are ignored.

Register one or more namespaces before resolving fixtures:

```php
use Fyre\TestSuite\Fixture\FixtureRegistry;

$fixtureRegistry->addNamespace('App\Fixtures');
$fixtureRegistry->addNamespace('Tests\Fixtures');
```

## Defining fixture data

Fixture classes extend `Fyre\TestSuite\Fixture\Fixture` and typically provide rows by setting the protected `$data` property.

The base fixture class provides:
- `data()` to return the dataset as an iterable
- `getClassAlias()` to determine the model alias for the fixture
- `getModel()` to resolve the model instance (cached per fixture instance)

### Class alias resolution

By default, the fixture class alias is derived from the fixture’s short class name by stripping the `Fixture` suffix:

- `ItemsFixture` → `Items`

To use a different model alias, set `protected string $classAlias` in the fixture class.

## Loading and cleaning up data

`Fixture::run()` iterates each row from `data()` and saves it through the model.

The fixture implementation creates entities with `guard: false` and `validate: false`, and saves them with `checkExists: false` and `checkRules: false`. If any row cannot be saved, `run()` throws a `RuntimeException`.

`Fixture::truncate()` truncates the underlying table using the model connection.

## Examples

### Creating a fixture

```php
namespace App\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;

class ItemsFixture extends Fixture
{
    protected iterable $data = [
        [
            'name' => 'Test 1',
        ],
        [
            'name' => 'Test 2',
        ],
    ];
}
```

### Resolving and running a fixture

```php
use Fyre\TestSuite\Fixture\FixtureRegistry;

$fixtureRegistry->addNamespace('App\Fixtures');

$fixtureRegistry->use('Items')->truncate();
$fixtureRegistry->use('Items')->run();
```

### Rebuilding a fixture instance

```php
use Fyre\TestSuite\Fixture\FixtureRegistry;

$fixtureRegistry->use('Items');

if ($fixtureRegistry->isLoaded('Items')) {
    $fixtureRegistry->unload('Items');
}

$fixtureRegistry->use('Items');
```

## Method guide

Methods below refer to `Fyre\TestSuite\Fixture\FixtureRegistry` and `Fyre\TestSuite\Fixture\Fixture`.

Most examples assume you already have a `$fixtureRegistry` instance.

### `FixtureRegistry`

#### **Add a namespace** (`addNamespace()`)

Register a namespace to search for fixture classes.

Arguments:
- `$namespace` (`string`): the namespace to search (normalized and deduplicated).

```php
$fixtureRegistry->addNamespace('App\Fixtures');
```

#### **Load a fixture** (`use()`)

Resolve a fixture by alias and return a shared instance (cached per alias).

Arguments:
- `$alias` (`string`): the fixture alias (without the `Fixture` suffix).

```php
$fixture = $fixtureRegistry->use('Items');
$fixture->run();
```

#### **Unload a fixture** (`unload()`)

Remove a cached fixture instance so it will be rebuilt next time it is used.

Arguments:
- `$alias` (`string`): the fixture alias to unload.

```php
$fixtureRegistry->unload('Items');
```

#### **Clear namespaces and fixtures** (`clear()`)

Clear all configured namespaces and unload all cached fixtures.

Arguments: (none)

```php
$fixtureRegistry->clear();
```

#### **Check whether a fixture is loaded** (`isLoaded()`)

Check whether the registry has already built and cached a fixture instance for an alias.

Arguments:
- `$alias` (`string`): the fixture alias to check.

```php
if ($fixtureRegistry->isLoaded('Items')) {
    $fixtureRegistry->unload('Items');
}
```

#### **Build a fixture** (`build()`)

Build a new fixture instance by alias without caching it.

Arguments:
- `$alias` (`string`): the fixture alias to build.

```php
$fixture = $fixtureRegistry->build('Items');
```

### `Fixture`

#### **Run the fixture** (`run()`)

Insert all rows returned by `data()` into the model table.

Arguments: (none)

```php
$fixtureRegistry->use('Items')->run();
```

#### **Truncate the fixture table** (`truncate()`)

Truncate the underlying table for the fixture’s model.

Arguments: (none)

```php
$fixtureRegistry->use('Items')->truncate();
```

#### **Return fixture rows** (`data()`)

Return the dataset used by `run()`. Most fixtures simply set the protected `$data` property.

Arguments: (none)

```php
namespace App\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;

final class ItemsFixture extends Fixture
{
    protected iterable $data = [
        ['name' => 'Test 1'],
    ];
}
```

#### **Resolve the model alias** (`getClassAlias()`)

Return the model alias for the fixture. By default, this is derived from the fixture class name by stripping the `Fixture` suffix.

Arguments: (none)

```php
$alias = $fixtureRegistry->use('Items')->getClassAlias();
```

#### **Resolve the model instance** (`getModel()`)

Return the model instance used by the fixture (cached per fixture instance).

Arguments: (none)

```php
$model = $fixtureRegistry->use('Items')->getModel();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Fixture discovery is namespace-order dependent: when multiple namespaces contain a fixture for the same alias, the first match wins.
- `$fixtureRegistry->use('ItemsFixture')` looks for an `ItemsFixtureFixture` class; use the alias without the suffix (`Items`).
- `FixtureRegistry::clear()` resets both the cached fixtures *and* the configured namespaces.
- `FixtureRegistry::use()` returns a shared instance per alias; call `unload()` to force a rebuild (including any constructor-injected dependencies).
- `Fixture::run()` creates entities with `guard: false` and `validate: false`, and saves them with `checkExists: false` and `checkRules: false`; database constraints can still cause saves to fail (and will throw).

## Related

- [Testing](index.md)
- [`TestCase`](test-case.md)
- [Integration Testing](integration.md)
