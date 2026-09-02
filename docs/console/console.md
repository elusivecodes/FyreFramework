# Console I/O

Use `Fyre\Console\Console` to write styled output, ask questions, render tables, and show progress from a command.

## Table of Contents

- [Start here](#start-here)
- [Common output](#common-output)
- [Prompting](#prompting)
- [Displaying tables and progress](#displaying-tables-and-progress)
- [Working with text](#working-with-text)
- [Streams and testing](#streams-and-testing)
- [API summary](#api-summary)
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

        return static::CODE_SUCCESS;
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

In CLI applications, omitted streams default to `STDIN`, `STDOUT`, and `STDERR`. Outside CLI, output defaults to `php://output` and errors use the same stream.

For tests, you can pass in-memory streams and assert against captured output:

```php
use Fyre\Console\Console;

$input = fopen('php://memory', 'r+');
$output = fopen('php://memory', 'w+');
$error = fopen('php://memory', 'w+');

$io = new Console($input, $output, $error);
```

See [Console Testing](../testing/console.md) for end-to-end command examples.

## API summary

### Output

| Method | Purpose |
| --- | --- |
| `write($text, $color = null, $background = null, $style = 0)` | write a styled line to stdout |
| `info($text, ...)` | write an informational line to stdout |
| `success($text, ...)` | write a success line to stdout |
| `warning($text, ...)` | write a warning line to stdout |
| `error($text, ...)` | write an error line to stderr |
| `comment($text, ...)` | write a dimmed line to stdout |

The convenience methods accept the same optional color, background, and style arguments as `write()`, with defaults suited to each message type.

### Interaction and display

| Method | Purpose |
| --- | --- |
| `input()` | read one line from stdin |
| `prompt($text)` | display a prompt and read a value |
| `confirm($text, $default = true)` | ask a yes/no question |
| `choice($text, $options, $default = null)` | select a value from a list |
| `table($data, $header = [])` | render an ASCII table |
| `progress($step = null, $totalSteps = 10)` | render or update a progress indicator |

### Static text helpers

| Method | Purpose |
| --- | --- |
| `Console::style($text, $color = null, $background = null, $style = 0)` | return text with ANSI styling |
| `Console::wrap($text, $maxWidth = null)` | wrap text to the terminal width or a lower limit |
| `Console::getWidth()` | get the terminal width, falling back to `80` |
| `Console::getHeight()` | get the terminal height, falling back to `24` |

## Behavior notes

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
