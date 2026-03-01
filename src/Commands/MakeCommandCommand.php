<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Override;

use function file_exists;
use function preg_replace;
use function strtolower;

/**
 * Implements the make command console command.
 *
 * Generates a console command class using the `command` stub.
 */
class MakeCommandCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:command';

    #[Override]
    protected string $description = 'Generate a new command.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the command',
            'required' => true,
        ],
        'alias' => [],
        'description' => [],
        'namespace' => [],
    ];

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see CommandRunner} namespace, or `App\Commands`.
     * When no alias is supplied, an alias is generated from the class name.
     *
     * @param Make $make The Make.
     * @param CommandRunner $commandRunner The CommandRunner.
     * @param Console $io The Console.
     * @param string $name The command name.
     * @param string|null $alias The command alias.
     * @param string|null $description The command description.
     * @param string|null $namespace The command namespace.
     * @return int|null The exit code.
     */
    public function run(Make $make, CommandRunner $commandRunner, Console $io, string $name, string|null $alias = null, string|null $description = null, string|null $namespace = null): int|null
    {
        $namespace ??= $commandRunner->getNamespaces()[0] ?? 'App\Commands';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Command');

        $path = $make->findPath($namespace);

        if (!$path) {
            $io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $io->error('Command file already exists.');

            return static::CODE_ERROR;
        }

        $command = (string) preg_replace('/Command$/', '', $className);
        $alias ??= strtolower((string) preg_replace('/(?<!^)([A-Z]+)/', '_$1', $command));

        $contents = Make::loadStub('command', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{alias}' => $alias,
            '{description}' => $description ?? '',
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Command file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
