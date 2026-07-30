# Console I/O

Use `Fyre\Console\Console` to write styled output, ask questions, render tables, and show progress from a command.

## Table of Contents

- [Start here](#start-here)
- [Common output](#common-output)
- [Prompting](#prompting)
- [Displaying tables and progress](#displaying-tables-and-progress)
- [Working with text](#working-with-text)
- [Streams and testing](#streams-and-testing)
- [Method guide](#method-guide)
  - [Output](#output)
  - [Prompts](#prompts)
  - [Tables and progress](#tables-and-progress)
  - [Text utilities](#text-utilities)
  - [Streams](#streams)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The usual way to use `Console` is from a command class through `$this->io`.

```php
use Fyre\Console\Command;

class ExampleCommand extends Command
{
    public function run(): int
    {
        $this->io->success('Finished.');

        return self::CODE_SUCCESS;
    }
}
```

If you need console I/O outside a command class, you can also construct `Console` directly.

## Common output

`Console` gives you a few convenience methods for common command output:

- `info()` for informational messages
- `success()` for success messages
- `warning()` for warnings
- `error()` for errors
- `comment()` for quieter supporting text

These methods write a line immediately. You can also use `write()` when you want to control colors or styles directly.

## Prompting

- `prompt()` asks for a free-form value
- `confirm()` asks a `y/n` question
- `choice()` asks the user to pick from a list

When `$options` is associative, `choice()` displays the descriptions but still returns the selected key.

## Displaying tables and progress

Use `table()` to print simple tabular output and `progress()` to show a single-line progress indicator.

`table()` expects each row to have the same number of columns. `progress(null)` clears the current indicator.

## Working with text

The static helpers are useful when you need to build strings before writing them:

- `Console::style()` applies ANSI styling
- `Console::wrap()` wraps text to the terminal width
- `Console::getWidth()` and `Console::getHeight()` return the current terminal size, or sensible defaults

Common constants:

- Colors: `Console::BLACK`, `Console::DARKGRAY`, `Console::GRAY`, `Console::WHITE`, `Console::RED`, `Console::GREEN`, `Console::YELLOW`, `Console::BLUE`, `Console::PURPLE`, `Console::CYAN`
- Styles: `Console::BOLD`, `Console::DIM`, `Console::ITALIC`, `Console::UNDERLINE`, `Console::FLASH`

## Streams and testing

`Console` reads from an input stream and writes to output and error streams.

For tests, you can pass in-memory streams and assert against captured output:

```php
use Fyre\Console\Console;

$input = fopen('php://memory', 'r+');
$output = fopen('php://memory', 'w+');
$error = fopen('php://memory', 'w+');

$io = new Console($input, $output, $error);
```

See [Console Testing](../testing/console.md) for end-to-end command examples.

## Method guide

This section focuses on the methods you are most likely to use in a command.

### Output

#### **Write output** (`write()`)

Write a line to the output stream. If the configured output stream is not a valid resource, this method does nothing.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int|null`): the text color (a `Console::*` constant).
- `$background` (`int|null`): the text background (a `Console::*` constant).
- `$style` (`int`): the text style (a `Console::*` constant).

```php
$io->write('Hello');
$io->write('Important', Console::WHITE, Console::RED, Console::BOLD);
```

#### **Write a status line** (`info()`)

Write a line to the output stream with a default informational color.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int`): the text color (default: `Console::BLUE`).
- `$background` (`int|null`): the text background (default: `null`).
- `$style` (`int`): the text style (default: `0`).

```php
$io->info('Starting…');
```

#### **Write a success line** (`success()`)

Write a line to the output stream with a default success color.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int`): the text color (default: `Console::GREEN`).
- `$background` (`int|null`): the text background (default: `null`).
- `$style` (`int`): the text style (default: `0`).

```php
$io->success('Done');
```

#### **Write a warning line** (`warning()`)

Write a line to the output stream with a default warning color.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int`): the text color (default: `Console::YELLOW`).
- `$background` (`int|null`): the text background (default: `null`).
- `$style` (`int`): the text style (default: `0`).

```php
$io->warning('This may take a while');
```

#### **Write an error line** (`error()`)

Write a line to the error stream. If the configured error stream is not a valid resource, this method does nothing.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int`): the text color (default: `Console::RED`).
- `$background` (`int|null`): the text background (default: `null`).
- `$style` (`int`): the text style (default: `0`).

```php
$io->error('Invalid option');
```

#### **Write a dim comment** (`comment()`)

Write a line to the output stream with a dim default style.

Arguments:
- `$text` (`string`): the text to write.
- `$color` (`int|null`): the text color (default: `null`).
- `$background` (`int|null`): the text background (default: `null`).
- `$style` (`int`): the text style (default: `Console::DIM`).

```php
$io->comment('Use --help to list options.');
```

### Prompts

#### **Prompt for input** (`prompt()`)

Write a prompt line (yellow) and read one line from the input stream.

Arguments:
- `$text` (`string`): the prompt text.

```php
$name = $io->prompt('Name:');
```

#### **Prompt for confirmation** (`confirm()`)

Prompt for a `y/n` confirmation.

Arguments:
- `$text` (`string`): the prompt text.
- `$default` (`bool`): the default choice when the user submits an empty response.

```php
if ($io->confirm('Continue?', true)) {
    $io->success('Continuing…');
}
```

#### **Prompt for a choice** (`choice()`)

Prompt for a choice and return the selected option.

Arguments:
- `$text` (`string`): the prompt text.
- `$options` (`array<int|string>`): the options (list of choices, or an associative array of `choice => description`).
- `$default` (`int|string|null`): the default choice when the user submits an empty response or enters an unknown option.

When `$options` is associative, the descriptions are displayed to the user, but the returned value is still the selected key.

```php
$environment = $io->choice('Environment', [
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
$io->table(
    [
        ['db:migrate', 'Run pending migrations'],
        ['db:rollback', 'Rollback the last batch'],
    ],
    ['Command', 'Description']
);
```

#### **Render a progress indicator** (`progress()`)

Render or update a single-line progress indicator. Repeated calls update the existing indicator, and `null` clears it.

Arguments:
- `$step` (`int|null`): the current step, or `null` to clear the indicator.
- `$totalSteps` (`int`): the total step count used to compute the percentage.

```php
$io->progress(1, 3);
$io->progress(2, 3);
$io->progress(3, 3);
$io->progress(null);
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

#### **Get terminal width** (`Console::getWidth()`)

Return the terminal width in characters, falling back to `80` when it cannot be determined.

```php
$width = Console::getWidth();
```

#### **Get terminal height** (`Console::getHeight()`)

Return the terminal height in characters, falling back to `24` when it cannot be determined.

```php
$height = Console::getHeight();
```

### Streams

#### **Read raw input** (`input()`)

Read one line from the input stream.

```php
$line = $io->input();
```

#### **Construct a `Console` instance** (`__construct()`)

Create a `Console` with custom input, output, and error streams. Under `cli`, omitted streams default to `STDIN`, `STDOUT`, and `STDERR`. Outside `cli`, output defaults to `php://output` and errors default to the same stream.

Arguments:
- `$input` (`resource|null`): the input stream.
- `$output` (`resource|null`): the output stream.
- `$error` (`resource|null`): the error stream.

```php
$io = new Console($input, $output, $error);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `choice()` compares user input case-insensitively and returns the default when no match is found.
- `choice()` preserves the original string or integer type of the selected option.
- `confirm()` returns `true` only when the user selects `y`.
- `input()` returns an empty string when no input stream is available.
- `progress(null)` clears the current indicator.
- `Console::getWidth()` and `Console::getHeight()` fall back to `80` and `24` when terminal size cannot be determined.

## Related

- [Console](index.md)
- [Console Commands](commands.md)
- [Console Testing](../testing/console.md)
