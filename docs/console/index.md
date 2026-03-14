# Console

Console covers running framework commands, generating files, and building interactive CLI tools.

## Table of Contents

- [Start here](#start-here)
- [Console overview](#console-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you are doing:

- **Running built-in commands**: start with [Console Commands](commands.md)
- **Generating classes and files**: see the [Make commands](commands.md#make-commands)
- **Writing your own command**: start with [Creating a command](commands.md#creating-a-command)
- **Printing output or prompting in a command**: see [Console I/O](console.md)

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
- [Database Migrations](../database/migrations.md) - migration commands and migration workflow
- [Queue Worker](../queue/worker.md) - running background workers from the console
