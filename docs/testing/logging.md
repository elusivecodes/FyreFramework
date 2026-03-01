# Log Testing

`LogTestTrait` captures log output during tests by registering in-memory `ArrayLogger` handlers, then provides assertions for matching messages (optionally scoped).

## Table of Contents

- [Purpose](#purpose)
- [How it works](#how-it-works)
- [Setting up handlers](#setting-up-handlers)
- [Asserting log output](#asserting-log-output)
- [Method guide](#method-guide)
  - [`LogTestTrait`](#logtesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `LogTestTrait` when you want to verify that code logged the right messages without writing to disk, and when you need to assert against both log levels and scopes.

## How it works

🧠 `setupLogs()` clears the current `LogManager` configuration and registers one or more `ArrayLogger` handlers. The assertion helpers read from every configured `ArrayLogger` that can handle the requested level and scope (see [Logging](../logging/index.md)).

## Setting up handlers

Call `setupLogs()` in your test’s `setUp()` method to register the handlers you want to capture.

`$logHandlers` supports two shapes:

- A simple list of log levels (each becomes a handler that captures only that level).
- An associative array of handler keys to options (each becomes a handler, and you supply options like `levels` and `scopes`).

Example:

```php
use Fyre\Log\LogManager;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\LogTestTrait;
use Override;

final class LoggingTest extends TestCase
{
    use LogTestTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupLogs([
            'error' => [
                'levels' => ['error'],
                'scopes' => ['payments'],
            ],
        ]);
    }

    public function testLogsScopedError(): void
    {
        $this->app->use(LogManager::class)
            ->handle('error', 'Card declined', scope: 'payments');

        $this->assertLogMessage('Card declined', 'error', 'payments');
    }
}
```

## Asserting log output

Use the assertion helpers to verify whether log output is present, matches exactly, or contains a substring.

```php
$this->assertLogIsEmpty('error');
$this->assertLogMessage('Card declined', 'error', 'payments');
$this->assertLogMessageContains('declined', 'error', 'payments');
```

## Method guide

Most examples assume you’re in a `TestCase` using `LogTestTrait`.

### `LogTestTrait`

#### **Set up in-memory log handlers** (`setupLogs()`)

Registers one or more `ArrayLogger` handlers and resets the `LogManager` config for the current test case.

Arguments:
- `$logHandlers` (`array`): handler definitions (levels and/or handler option arrays).

```php
$this->setupLogs(['error', 'warning']);
```

#### **Assert no messages were logged** (`assertLogIsEmpty()`)

Asserts that no log messages were captured for the given level (and optional scope).

Arguments:
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogIsEmpty('error');
```

#### **Assert an exact message was logged** (`assertLogMessage()`)

Asserts that a log message exactly matches the expected message for the given level (and optional scope).

Arguments:
- `$expectedMessage` (`string`): the expected log message.
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogMessage('Card declined', 'error', 'payments');
```

#### **Assert a message contains a string** (`assertLogMessageContains()`)

Asserts that at least one log message contains the provided substring for the given level (and optional scope).

Arguments:
- `$needle` (`string`): the substring to search for.
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogMessageContains('declined', 'error', 'payments');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Scope matching is strict: assertions only read from handlers that can handle both the level and the scope.
- `setupLogs()` clears the current `LogManager` config; call it after any setup that depends on your normal logging configuration.
- When you use associative keys in `$logHandlers`, always provide `levels`: if you omit it, the key value is used as the default `levels` value, which usually won’t match real log levels.

## Related

- [Testing](index.md)
- [Logging](../logging/index.md)
