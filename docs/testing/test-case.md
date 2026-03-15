# `TestCase`

Use `Fyre\TestSuite\TestCase` as the base class for framework-powered PHPUnit tests.

It gives each test access to the shared application engine, clears scoped services before each test, and can load fixtures automatically when you opt in.

## Table of Contents

- [Start here](#start-here)
- [Using fixtures](#using-fixtures)
- [Method guide](#method-guide)
  - [`TestCase`](#testcase)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Extend `TestCase` when your test needs the framework engine or any of the testing traits.

`TestCase` expects a shared `Engine` instance to already be available through `Engine::getInstance()`.

```php
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\IntegrationTestTrait;

final class HealthcheckTest extends TestCase
{
    use IntegrationTestTrait;

    public function testHealthcheck(): void
    {
        $this->get('/health');
        $this->assertResponseContains('OK');
    }
}
```

## Using fixtures

`TestCase` can apply fixtures before each test and clean them up after by setting the `$fixtures` property:

```php
use Fyre\TestSuite\TestCase;

final class UsersTableTest extends TestCase
{
    protected array $fixtures = ['Users'];
}
```

For fixture definitions, discovery rules, and examples, see [Fixtures](fixtures.md).

When cleanup runs, `TestCase` truncates the fixture tables plus any tables implied by the fixtures' configured associations, with foreign key checks temporarily disabled on the active database connection.

## Method guide

Most examples assume you’re in a `TestCase`.

### `TestCase`

#### **Skip a test when a condition is true** (`skipIf()`)

Skip the current test when the condition is true.

Arguments:
- `$condition` (`bool`): whether to skip the test.
- `$message` (`string`): the skip message to display.

```php
$this->skipIf(!extension_loaded('pdo_mysql'), 'pdo_mysql is required for this test.');
```

#### **Skip a test unless a condition is true** (`skipUnless()`)

Skip the current test unless the condition is true.

Arguments:
- `$condition` (`bool`): whether the test can run.
- `$message` (`string`): the skip message to display.

```php
$this->skipUnless(PHP_VERSION_ID >= 80500, 'PHP 8.5+ is required for this test.');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- When fixtures are enabled via `$fixtures`, `TestCase` applies fixtures before each test and truncates the fixture tables plus any tables implied by their configured associations after each test, with foreign key checks temporarily disabled on the active database connection.
- `TestCase` uses the existing shared `Engine` instance from `Engine::getInstance()` and does not boot a fresh application by itself.
- `TestCase` calls `$this->app->clearScoped()` in `setUp()`, so scoped services do not leak across tests.

## Related

- [Testing](index.md)
- [Fixtures](fixtures.md)
- [Constraints](constraints.md)
- [Integration Testing](integration.md)
