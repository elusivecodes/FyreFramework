# Console

Discover and run framework CLI commands with consistent argument parsing, interactive prompting, and lifecycle events.

## Table of Contents

- [Console overview](#console-overview)
- [How `CommandRunner` Runs Commands](#how-commandrunner-runs-commands)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Console overview

The console subsystem discovers command classes, parses `argv`, and runs commands through the container so services can be injected consistently. In a typical application, `CommandRunner` is resolved from the container, usually via `Engine`, with the default command namespaces already registered.

It is built around three types:

- `Fyre\Console\CommandRunner` discovers commands, resolves a command alias, parses arguments, and executes the command through the container.
- `Fyre\Console\Command` is the base class for commands, exposing default `alias`, `description`, and `options` values used during discovery.
- `Fyre\Console\Console` provides terminal I/O (tables, prompts, and styled output) used by both the runtime and command classes (see [Console I/O](console.md)).

## How `CommandRunner` Runs Commands

At a high level, the console runtime works like this:

1. **Discover commands.** `CommandRunner` scans registered namespaces for `*Command.php` classes, then reflects default `alias`, `description`, and `options` values. The resulting command list is cached until cleared.
2. **Parse argv.** `handle()` reads the command alias and parses options from `--option value` / `-o value`. It normalizes option names to lower camelCase and keeps remaining args as positional values.
3. **Resolve option values.** `CommandRunner` applies named arguments, fills remaining positional values in option order, then uses defaults, type parsing, and prompts when needed.
4. **Execute through the container.** `run()` invokes the command’s `run()` method through the container so option values can be matched by parameter name and other parameters can be injected as services.
5. **Dispatch lifecycle events.** Command discovery and execution dispatch `Command.buildCommands`, `Command.beforeExecute`, and `Command.afterExecute` (see [Events](../events/index.md)).

## Pages in this section

- [Console Commands](commands.md) — built-in command list, options, and examples.
- [Console I/O](console.md) — prompts, styled output, tables, and progress output.

## Related

- [Events](../events/index.md) — command lifecycle events (`Command.buildCommands`, `Command.beforeExecute`, `Command.afterExecute`).
- [Console Testing](../testing/console.md) — execute commands and assert output in tests.
