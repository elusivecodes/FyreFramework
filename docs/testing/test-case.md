# `TestCase`

`TestCase` is the base PHPUnit test case for framework-powered tests. It boots an application `Engine` for each test and integrates with fixtures when you opt in.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Using fixtures](#using-fixtures)
- [Method guide](#method-guide)
  - [`TestCase`](#testcase)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Extend `Fyre\TestSuite\TestCase` when you want your tests to run against the framework engine, and when you want the option to apply fixtures automatically per test.

## Quick start

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

`TestCase` can apply fixtures before each test and truncate them after by setting the `$fixtures` property:

```php
use Fyre\TestSuite\TestCase;

final class UsersTableTest extends TestCase
{
    protected array $fixtures = ['Users'];
}
```

For full fixture definitions, discovery rules, and examples, see [Fixtures](fixtures.md).

## Method guide

Most examples assume you’re in a `TestCase`.

### `TestCase`

#### **Skip a test when a condition is true** (`skipIf()`)

Skip the current test by calling PHPUnit’s `markTestSkipped()` when the condition is true.

Arguments:
- `$condition` (`bool`): whether to skip the test.
- `$message` (`string`): the skip message to display.

```php
$this->skipIf(!extension_loaded('pdo_mysql'), 'pdo_mysql is required for this test.');
```

#### **Skip a test unless a condition is true** (`skipUnless()`)

Skip the current test by calling PHPUnit’s `markTestSkipped()` unless the condition is true.

Arguments:
- `$condition` (`bool`): whether the test can run.
- `$message` (`string`): the skip message to display.

```php
$this->skipUnless(PHP_VERSION_ID >= 80500, 'PHP 8.5+ is required for this test.');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- When fixtures are enabled via `$fixtures`, `TestCase` applies and truncates fixtures with foreign key checks temporarily disabled (via the active database connection).
- `TestCase` calls `$this->app->clearScoped()` in `setUp()`, so scoped services do not leak across tests.

## Related

- [Testing](index.md)
- [Fixtures](fixtures.md)
- [Constraints](constraints.md)
- [Integration Testing](integration.md)
