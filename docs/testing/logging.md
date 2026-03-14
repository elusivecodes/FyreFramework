# Log Testing

Use `LogTestTrait` when you want to verify log output without writing to disk.

The trait registers in-memory `ArrayLogger` handlers and gives you assertions for exact messages, partial matches, and empty logs.

## Table of Contents

- [Start here](#start-here)
- [Setting up handlers](#setting-up-handlers)
- [Asserting log output](#asserting-log-output)
- [Method guide](#method-guide)
  - [`LogTestTrait`](#logtesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Call `setupLogs()` in `setUp()`.
2. Choose the levels or handler configs you want to capture.
3. Run the code that logs.
4. Assert on the captured messages.

## Setting up handlers

Call `setupLogs()` in your test's `setUp()` method to register the handlers you want to capture.

`$logHandlers` supports two shapes:

- a simple list of log levels, where each level becomes its own handler
- an associative array of handler keys to full option arrays, where you can set `levels` and `scopes`

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

Register one or more `ArrayLogger` handlers for the current test case.

Arguments:
- `$logHandlers` (`array`): handler definitions (levels and/or handler option arrays).

```php
$this->setupLogs(['error', 'warning']);
```

#### **Assert no messages were logged** (`assertLogIsEmpty()`)

Assert that no log messages were captured for the given level and optional scope.

Arguments:
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogIsEmpty('error');
```

#### **Assert an exact message was logged** (`assertLogMessage()`)

Assert that a log message exactly matches the expected message for the given level and optional scope.

Arguments:
- `$expectedMessage` (`string`): the expected log message.
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogMessage('Card declined', 'error', 'payments');
```

#### **Assert a message contains a string** (`assertLogMessageContains()`)

Assert that at least one log message contains the provided substring for the given level and optional scope.

Arguments:
- `$needle` (`string`): the substring to search for.
- `$level` (`string`): the log level to assert against.
- `$scope` (`string|null`): the log scope to assert against.
- `$message` (`string`): the message to display on failure.

```php
$this->assertLogMessageContains('declined', 'error', 'payments');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Scope matching is strict, so assertions only read from handlers that can handle both the level and the scope.
- `setupLogs()` clears the current `LogManager` config, so call it after any setup that depends on your normal logging configuration.
- When you use associative keys in `$logHandlers`, provide `levels`; otherwise the key name becomes the default `levels` value.

## Related

- [Testing](index.md)
- [Logging](../logging/index.md)
