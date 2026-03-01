# Console I/O

`Fyre\Console\Console` is a lightweight console I/O facade used by the console runtime and command classes to print styled output, prompt for input, and render tables.

## Table of Contents

- [Purpose](#purpose)
- [Where `Console` Fits](#where-console-fits)
- [Styled Output](#styled-output)
- [Prompts and Choices](#prompts-and-choices)
- [Tables](#tables)
- [Progress Output](#progress-output)
- [Text Styling and Wrapping](#text-styling-and-wrapping)
- [Streams and Testing](#streams-and-testing)
- [Method guide](#method-guide)
  - [Output](#output)
  - [Prompts](#prompts)
  - [Tables and progress](#tables-and-progress)
  - [Text utilities](#text-utilities)
  - [Streams](#streams)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Console` when you want consistent terminal output and prompting across commands:

- Styled output (`info()`, `success()`, `warning()`, `error()`, `comment()`).
- Interactive prompts (`prompt()`, `confirm()`, `choice()`).
- Simple tables and progress indicators (`table()`, `progress()`).

## Where `Console` Fits

`Fyre\Console\CommandRunner` uses a `Console` instance to:

- Print errors (for example, invalid command aliases or option values).
- Prompt for required options when they are missing.
- Render the command list table when no alias is provided.

Commands can also accept `Console` as a dependency (for example as a `run()` parameter) to share the same streams and styling rules as the runtime. For the broader console subsystem, see [Console](index.md).

## Styled Output

Most output methods write to the standard output stream via `write()`. `error()` writes to the error stream. Each method applies ANSI styling via `Console::style()`:

- `info()` defaults to blue
- `success()` defaults to green
- `warning()` defaults to yellow
- `comment()` defaults to dim text
- `error()` defaults to red and writes to the error stream

You can override the color, background, and style on any call.

## Prompts and Choices

- `prompt()` writes a prompt line (yellow), then reads a single line from the input stream.
- `choice()` prompts for a choice and returns the selected option (or the default).
  - When `$options` is a list, its values are the choices.
  - When `$options` is associative, keys are the choices and values are displayed as descriptions.
- `confirm()` is a yes/no prompt implemented via `choice()`.

## Tables

`table()` renders a simple ASCII table.

A few constraints apply:

- Each `$data` row must have the same number of columns.
- When `$header` is provided it is rendered as the first row and separated by a horizontal border.
- ANSI style codes inside cell text are preserved, but do not affect column width calculation.

## Progress Output

`progress()` prints a single-line progress indicator, updating the current terminal line as it advances.

## Text Styling and Wrapping

Use the static helpers when you need to produce styled/wrapped strings before writing them:

- `Console::style()` wraps text with ANSI escape codes
- `Console::wrap()` wraps text to the terminal width (or a smaller max width)
- `Console::getWidth()` and `Console::getHeight()` query terminal size (falling back to `80` and `24` when unavailable)

Common constants:

- Colors: `Console::BLACK`, `Console::RED`, `Console::GREEN`, `Console::YELLOW`, `Console::BLUE`, `Console::PURPLE`, `Console::CYAN`, `Console::WHITE`
- Styles: `Console::BOLD`, `Console::DIM`, `Console::ITALIC`, `Console::UNDERLINE`, `Console::FLASH`

For background, pass a color constant as the `$background` argument.

## Streams and Testing

`Console` reads from an input stream and writes to output and error streams.

- Under `cli`, it defaults to `STDIN`, `STDOUT`, and `STDERR`
- Outside `cli`, it writes to `php://output` and uses the output stream for errors by default

For tests, you can construct a `Console` with in-memory streams and assert against captured output:

```php
use Fyre\Console\Console;

$input = fopen('php://memory', 'r+');
$output = fopen('php://memory', 'w+');
$error = fopen('php://memory', 'w+');

$console = new Console($input, $output, $error);
```

## Method guide

This section focuses on the methods you’ll use most when writing console commands.

### Output

#### **Write output** (`write()`)

Write a line to the output stream.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int|null`): the text color (a `Console::*` constant).
- `$background` (`int|null`): the text background (a `Console::*` constant).
- `$style` (`int`): the text style (a `Console::*` constant).

```php
$console->write('Hello');
$console->write('Important', Console::WHITE, Console::RED, Console::BOLD);
```

#### **Write a status line** (`info()`)

Write a line to the output stream with a default informational color.

Arguments:
- `$text` (`string`): the text to write.

```php
$console->info('Starting…');
```

#### **Write a success line** (`success()`)

Write a line to the output stream with a default success color.

Arguments:
- `$text` (`string`): the text to write.

```php
$console->success('Done');
```

#### **Write a warning line** (`warning()`)

Write a line to the output stream with a default warning color.

Arguments:
- `$text` (`string`): the text to write.

```php
$console->warning('This may take a while');
```

#### **Write an error line** (`error()`)

Write a line to the error stream.

Arguments:
- `$text` (`string`): the text to write.

```php
$console->error('Invalid option');
```

#### **Write a dim comment** (`comment()`)

Write a line to the output stream with a dim default style.

Arguments:
- `$text` (`string`): the text to write.

```php
$console->comment('Use --help to list options.');
```

### Prompts

#### **Prompt for input** (`prompt()`)

Write a prompt line (yellow) and read one line from the input stream.

Arguments:
- `$text` (`string`): the prompt text.

```php
$name = $console->prompt('Name:');
```

#### **Prompt for confirmation** (`confirm()`)

Prompt for a `y/n` confirmation.

Arguments:
- `$text` (`string`): the prompt text.
- `$default` (`bool`): the default choice when the user submits an empty response.

```php
if ($console->confirm('Continue?', true)) {
    $console->success('Continuing…');
}
```

#### **Prompt for a choice** (`choice()`)

Prompt for a choice and return the selected option.

Arguments:
- `$text` (`string`): the prompt text.
- `$options` (`array`): the options (list of choices, or an associative array of `choice => description`).
- `$default` (`int|string|null`): the default choice when the user submits an empty response or enters an unknown option.

```php
$environment = $console->choice('Environment', [
    'dev' => 'Development',
    'prod' => 'Production',
], 'dev');
```

### Tables and progress

#### **Render a table** (`table()`)

Render an ASCII table to the output stream.

Arguments:
- `$data` (`array`): table rows.
- `$header` (`array`): optional header row.

```php
$console->table(
    [
        ['db:migrate', 'Run pending migrations'],
        ['db:rollback', 'Rollback the last batch'],
    ],
    ['Command', 'Description']
);
```

#### **Render a progress indicator** (`progress()`)

Render or update a single-line progress indicator.

Arguments:
- `$step` (`int|null`): the current step, or `null` to clear the indicator.
- `$totalSteps` (`int`): the total step count used to compute the percentage.

```php
$console->progress(1, 3);
$console->progress(2, 3);
$console->progress(3, 3);
$console->progress(null);
```

### Text utilities

#### **Style a string** (`Console::style()`)

Return a styled string using ANSI escape codes.

Arguments:
- `$text` (`string`): the text.
- `$color` (`int|null`): the text color (a `Console::*` constant).
- `$background` (`int|null`): the background color (a `Console::*` constant).
- `$style` (`int`): the style (a `Console::*` constant).

```php
$message = Console::style('OK', Console::GREEN, style: Console::BOLD);
```

#### **Wrap a string** (`Console::wrap()`)

Wrap a string to the terminal width (or a smaller max width).

Arguments:
- `$text` (`string`): the text.
- `$maxWidth` (`int|null`): the maximum width.

```php
$text = Console::wrap('A long line that should wrap automatically.');
```

### Streams

#### **Read raw input** (`input()`)

Read one line from the input stream.

```php
$line = $console->input();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `choice()` compares user input case-insensitively and returns the default when no match is found.
- `confirm()` returns `true` only when the user selects `y`.
- `write()` and `error()` do nothing if the configured output stream is not a valid resource.
- `progress(null)` clears the indicator and emits terminal control sequences (and an audible bell in many terminals).
- `Console::getWidth()` and `Console::getHeight()` depend on `tput` when available and fall back to default sizes when terminal size can’t be resolved.

## Related

- [Console](index.md)
- [Built-in Console Commands](commands.md)
- [Console Testing](../testing/console.md)
- [Events](../events/index.md)
