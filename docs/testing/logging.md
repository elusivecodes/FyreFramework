# Log Testing

Use `LogTestTrait` to replace configured loggers with in-memory `ArrayLogger` handlers and assert on captured messages without writing to disk.

Unlike the other testing traits, log capture must be configured explicitly in `setUp()`.

## Table of Contents

- [Configure log capture](#configure-log-capture)
- [Method guide](#method-guide)
  - [`LogTestTrait`](#logtesttrait)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Configure log capture

Call `setupLogs()` once from the test's `setUp()` method, then log through the normal `LogManager`:

```php
use Fyre\Log\LogManager;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\LogTestTrait;
use Override;

final class PaymentLoggingTest extends TestCase
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

    public function testLogsDeclinedPayment(): void
    {
        $this->app->use(LogManager::class)
            ->handle(
                'error',
                'Card declined',
                scope: 'payments'
            );

        $this->assertLogMessage(
            'Card declined',
            'error',
            'payments'
        );
    }
}
```

A list such as `['error', 'warning']` creates one handler per level. An associative entry accepts normal logger options such as `levels` and `scopes`; every configured handler uses `ArrayLogger` regardless of any supplied `className`.

## Method guide

The test class above provides the setup for every assertion in this reference.

### `LogTestTrait`

#### **Set up in-memory handlers** (`setupLogs()`)

```php
setupLogs(array $logHandlers = []): void
```

The method clears the current `LogManager` configuration, remembers it for teardown, and registers the requested in-memory handlers.

#### **Assert captured messages**

| Assertion | Checks |
| --- | --- |
| `assertLogIsEmpty($level, $scope = null, $message = '')` | no captured messages for the level and optional scope |
| `assertLogMessage($expected, $level, $scope = null, $message = '')` | at least one complete message equals `$expected` |
| `assertLogMessageContains($needle, $level, $scope = null, $message = '')` | at least one message contains `$needle` |

Scope matching is strict. An assertion only reads handlers whose level and scope configuration can handle both supplied values.

## Behavior notes

- Call `setupLogs()` after setup that depends on the application's normal logger configuration because it clears that configuration.
- Associative handler definitions should provide `levels`; otherwise the associative key becomes the default level.
- The trait restores the original `LogManager` configuration after each test.
- Exact assertions compare complete formatted messages; use `assertLogMessageContains()` only when surrounding context is intentionally variable.

## Related

- [Testing](index.md)
- [Logging](../logging/index.md)
- [Constraints](constraints.md)
