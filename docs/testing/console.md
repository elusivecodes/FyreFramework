# Console Testing

Use `ConsoleTestTrait` when you want to run console commands in tests and assert on stdout, stderr, and exit codes.

The trait captures command I/O in memory so you can test commands without spawning a separate process.

## Table of Contents

- [Start here](#start-here)
- [Running commands](#running-commands)
- [Feeding input](#feeding-input)
- [Method guide](#method-guide)
  - [Execution](#execution)
  - [Exit codes](#exit-codes)
  - [Standard output](#standard-output)
  - [Standard error](#standard-error)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual workflow is:

1. Call `exec()` with the command string you want to run.
2. Optionally pass input lines for interactive prompts.
3. Assert on the exit code, stdout, or stderr.

## Running commands

```php
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\ConsoleTestTrait;

final class ConsoleRunnerTest extends TestCase
{
    use ConsoleTestTrait;

    public function testInvalidCommand(): void
    {
        $this->exec('invalid');

        $this->assertExitError();
        $this->assertErrorContains('Invalid command: invalid');
    }
}
```

`exec()` runs the command in-process and stores the exit code plus captured stdout and stderr for later assertions.

## Feeding input

Pass input lines as the second argument to `exec()` when the command reads from stdin:

```php
$this->exec('confirm-delete', [
    'yes',
]);
```

## Method guide

The test class under [Running commands](#running-commands) provides the setup for this reference. Every assertion accepts an optional final `$message` argument for the PHPUnit failure message.

### Execution

`exec(string $command, array $input = []): void` runs a command in-process, replacing the previously captured exit code and output. Each input value becomes one line on stdin.

### Exit codes

| Assertion | Checks |
| --- | --- |
| `assertExitSuccess()` | exit code is `Command::CODE_SUCCESS` |
| `assertExitError()` | exit code is `Command::CODE_ERROR` |
| `assertExitCode($code)` | exact exit code |

### Standard output

| Assertion | Checks |
| --- | --- |
| `assertOutputContains($value)` | a stdout line contains a string |
| `assertOutputNotContains($value)` | no stdout line contains a string |
| `assertOutputRegExp($pattern)` | a stdout line matches a regular expression |
| `assertOutputContainsRow($value)` | stdout contains the given table row |
| `assertOutputEmpty()` | nothing was written to stdout |

### Standard error

| Assertion | Checks |
| --- | --- |
| `assertErrorContains($value)` | a stderr line contains a string |
| `assertErrorRegExp($pattern)` | a stderr line matches a regular expression |
| `assertErrorEmpty()` | nothing was written to stderr |

## Behavior notes

- `exec()` treats `$input` as a list of lines and appends `PHP_EOL` to each line before running the command.
- The command string is split using `str_getcsv($command, ' ', '"', '\\')`, so quoting with `"` and escaping with `\` are supported.
- `CommandRunner` is resolved from the engine container during setup; if your test commands live outside the default namespaces, add them to `$this->runner` before calling `exec()`.
- The trait restores the original `Console` and closes its in-memory streams after each test.

## Related

- [Testing](index.md)
- [Integration Testing](integration.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)
