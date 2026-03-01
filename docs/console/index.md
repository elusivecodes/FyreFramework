# Console

🧭 Discover and run framework CLI commands with consistent argument parsing, interactive prompting, and lifecycle events.

## Table of Contents

- [Start here](#start-here)
- [Console overview](#console-overview)
- [How `CommandRunner` Runs Commands](#how-commandrunner-runs-commands)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Running built-in commands**: see [Built-in Console Commands](commands.md) (aliases, options, and examples).
- **Printing output, prompting, and rendering tables**: see [Console I/O](console.md).
- **Creating your own commands**: start with [Built-in Console Commands → Custom commands](commands.md#custom-commands).

## Console overview

🧩 The console subsystem discovers command classes, parses `argv`, and runs commands through the container so services can be injected consistently.

It is built around three types:

- `Fyre\Console\CommandRunner` resolves a command alias, parses arguments, and executes the command through the container.
- `Fyre\Console\Command` is the base class for commands, exposing default `alias`, `description`, and `options` values used during discovery.
- `Fyre\Console\Console` provides terminal I/O (tables, prompts, and styled output) used by the runtime and commands (see [Console I/O](console.md)).

## How `CommandRunner` Runs Commands

At a high level, the console runtime works like this:

1) **Discover commands.** `CommandRunner` scans registered namespaces for `*Command.php` classes, then reflects default `alias`, `description`, and `options` values. The resulting command list is cached until cleared.
2) **Parse argv.** `handle()` reads the command alias and parses options from `--option value` / `-o value`. It normalizes option names to lower camelCase and keeps remaining args as positional values.
3) **Resolve option values.** When required options are missing, `CommandRunner` uses `Console` prompts (including `choice()` for constrained values and `confirm()` for booleans).
4) **Execute through the container.** `run()` invokes the command’s `run()` method through the container so services can be injected and option values can be supplied.
5) **Dispatch lifecycle events.** Command discovery and execution dispatch `Command.buildCommands`, `Command.beforeExecute`, and `Command.afterExecute` (see [Events](../events/index.md)).

## Pages in this section

- [Built-in Console Commands](commands.md) — built-in command list, options, and examples.
- [Console I/O](console.md) — prompts, styled output, tables, and progress output.

## Related

- [Events](../events/index.md) — command lifecycle events (`Command.buildCommands`, `Command.beforeExecute`, `Command.afterExecute`).
- [Console Testing](../testing/console.md) — execute commands and assert output in tests.
