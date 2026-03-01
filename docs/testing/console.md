# Console Testing

`ConsoleTestTrait` runs console commands through `Fyre\Console\CommandRunner` and captures stdout/stderr and exit codes for assertions.

## Table of Contents

- [Purpose](#purpose)
- [How it works](#how-it-works)
- [Example: invalid command handling](#example-invalid-command-handling)
- [Method guide](#method-guide)
  - [Running commands](#running-commands)
  - [Exit code assertions](#exit-code-assertions)
  - [Stdout assertions](#stdout-assertions)
  - [Stderr assertions](#stderr-assertions)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `ConsoleTestTrait` in PHPUnit tests when you want to execute framework console commands and assert against captured output and exit codes.

## How it works

🧠 `ConsoleTestTrait` runs commands in-process and stores the captured stdout/stderr and exit code so you can make assertions after execution.

- Sets up in-memory streams for stdin/stdout/stderr before each test.
- Registers a `Fyre\Console\Console` instance in the engine container so command output is captured.
- Parses the command string using `str_getcsv()` (space-delimited) to support quoting and escaping.

## Example: invalid command handling

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

## Method guide

Most examples assume you’re in a `TestCase` using `ConsoleTestTrait`.

### Running commands

#### **Run a command** (`exec()`)

Runs a console command through `CommandRunner`, capturing stdout/stderr and storing the exit code for later assertions.

Arguments:
- `$command` (`string`): the full command string (command alias plus arguments).
- `$input` (`array<int, string>`): lines to write to stdin before running the command.

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
$this->exec('invalid');
$this->assertOutputContains('Invalid command: invalid');
```

#### **Assert stdout does not contain** (`assertOutputNotContains()`)

Asserts that no captured stdout lines contain a value.

Arguments:
- `$value` (`string`): the value that must not appear.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertOutputNotContains('All good');
```

#### **Assert stdout matches pattern** (`assertOutputRegExp()`)

Asserts that at least one captured stdout line matches a regex pattern.

Arguments:
- `$pattern` (`string`): the expected pattern.
- `$message` (`string`): the message to display on failure.

```php
$this->exec('invalid');
$this->assertOutputRegExp('/Invalid command:/');
```

#### **Assert stdout contains table row** (`assertOutputContainsRow()`)

Asserts that captured stdout contains a row with the expected cell values.

Arguments:
- `$value` (`array<int, mixed>`): the expected row values.
- `$message` (`string`): the message to display on failure.

```php
fwrite($this->output, '| a | b | c |'.PHP_EOL);
$this->assertOutputContainsRow(['a', 'b', 'c']);
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

⚠️ A few behaviors are worth keeping in mind:

- `exec()` treats `$input` as a list of lines and appends `PHP_EOL` to each line before running the command.
- The command string is split using `str_getcsv($command, ' ', '"', '\\')`, which supports quoting with `"` and escaping with `\`.
- `CommandRunner` is resolved from the engine container during setup; if your test commands live outside the default namespaces, add them to `$this->runner` before calling `exec()`.

## Related

- [Testing](index.md)
- [Integration Testing](integration.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)
