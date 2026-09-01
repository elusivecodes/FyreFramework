# Console

Console covers running framework commands, generating files, and building interactive CLI tools.

## Table of Contents

- [Command workflow](#command-workflow)
- [Console overview](#console-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Command workflow

Run the application entry point without a command, or with `--help`, to list available commands. [Console Commands](commands.md) documents the built-in commands, `make:*` generators, command options, and custom command classes.

Inside a command, use [Console I/O](console.md) for styled output, tables, progress indicators, and prompts. Non-interactive commands should avoid prompts unless the operation needs confirmation.

## Console overview

The console tools revolve around three classes:

- `Fyre\Console\CommandRunner` runs commands by alias or from an `argv` array
- `Fyre\Console\Command` is the base class for your commands
- `Fyre\Console\Console` handles output, prompts, tables, and progress indicators

If you are writing a command class, extend `Fyre\Console\Command`, define an alias and options, and use `$this->io` for terminal I/O.

## Pages in this section

- [Console Commands](commands.md) - running built-in commands, using `make:*`, and creating custom commands
- [Console I/O](console.md) - prompts, styled output, tables, and progress indicators

## Related

- [Console Testing](../testing/console.md) - execute commands and assert output in tests
- [Database migrations](../database/migrations.md) - migration commands and migration workflow
- [Queue Worker](../queue/worker.md) - running queue workers from the console
