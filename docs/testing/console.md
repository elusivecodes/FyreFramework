# Console Testing

Use `ConsoleTestTrait` when you want to run console commands in tests and assert on stdout, stderr, and exit codes.

The trait captures command I/O in memory so you can test commands without spawning a separate process.

## Table of Contents

- [Start here](#start-here)
- [Running commands](#running-commands)
- [Feeding input](#feeding-input)
- [Method guide](#method-guide)
  - [Command execution](#command-execution)
  - [Exit code assertions](#exit-code-assertions)
  - [Stdout assertions](#stdout-assertions)
  - [Stderr assertions](#stderr-assertions)
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

Most examples assume you’re in a `TestCase` using `ConsoleTestTrait`.

### Command execution

#### **Run a command** (`exec()`)

Run a console command through `CommandRunner`, capturing stdout, stderr, and the exit code.

Arguments:
- `$command` (`string`): the full command string (command alias plus arguments).
- `$input` (`string[]`): lines to write to stdin before running the command.

```php
$this->exec('arguments --value value');
$this->assertExitSuccess();
```

### Exit code assertions

#### **Assert success exit code** (`assertExitSuccess()`)

Asserts that the last executed command exited with `Command::CODE_SUCCESS`.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->exec('arguments --value value');
$this->assertExitSuccess();
```

#### **Assert error exit code** (`assertExitError()`)

Asserts that the last executed command exited with `Command::CODE_ERROR`.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertExitError();
```

#### **Assert specific exit code** (`assertExitCode()`)

Asserts that the last executed command exited with a specific code.

Arguments:
- `$code` (`int`): the expected exit code.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertExitCode(1);
```

### Stdout assertions

#### **Assert stdout contains** (`assertOutputContains()`)

Asserts that at least one captured stdout line contains a value.

Arguments:
- `$value` (`string`): the expected value.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('users:list');
$this->assertOutputContains('user@example.com');
```

#### **Assert stdout does not contain** (`assertOutputNotContains()`)

Asserts that no captured stdout lines contain a value.

Arguments:
- `$value` (`string`): the value that must not appear.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('users:list');
$this->assertOutputNotContains('disabled@example.com');
```

#### **Assert stdout matches pattern** (`assertOutputRegExp()`)

Asserts that at least one captured stdout line matches a regex pattern.

Arguments:
- `$pattern` (`string`): the expected pattern.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('users:list');
$this->assertOutputRegExp('/\d+ users/');
```

#### **Assert stdout contains table row** (`assertOutputContainsRow()`)

Asserts that captured stdout contains a row with the expected cell values.

Arguments:
- `$value` (`array<int, mixed>`): the expected row values.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('users:list');
$this->assertOutputContainsRow(['1', 'user@example.com', 'active']);
```

#### **Assert stdout is empty** (`assertOutputEmpty()`)

Asserts that no lines were written to stdout.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->assertOutputEmpty();
```

### Stderr assertions

#### **Assert stderr contains** (`assertErrorContains()`)

Asserts that at least one captured stderr line contains a value.

Arguments:
- `$value` (`string`): the expected value.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertErrorContains('Invalid command: invalid');
```

#### **Assert stderr matches pattern** (`assertErrorRegExp()`)

Asserts that at least one captured stderr line matches a regex pattern.

Arguments:
- `$pattern` (`string`): the expected pattern.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertErrorRegExp('/Invalid command:/');
```

#### **Assert stderr is empty** (`assertErrorEmpty()`)

Asserts that no lines were written to stderr.

Arguments:
- `$message` (`string`): the message to display on failure.

```php
$this->exec('arguments --value value');
$this->assertErrorEmpty();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `exec()` treats `$input` as a list of lines and appends `PHP_EOL` to each line before running the command.
- The command string is split using `str_getcsv($command, ' ', '"', '\\')`, so quoting with `"` and escaping with `\` are supported.
- `CommandRunner` is resolved from the engine container during setup; if your test commands live outside the default namespaces, add them to `$this->runner` before calling `exec()`.
- The trait restores the original `Console` and closes its in-memory streams after each test.

## Related

- [Testing](index.md)
- [Integration Testing](integration.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)
