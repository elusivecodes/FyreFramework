<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Override;

use function file_exists;

/**
 * Implements the make controller console command.
 *
 * Generates a controller class using the `controller` stub.
 */
class MakeControllerCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:controller';

    #[Override]
    protected string $description = 'Generate a new controller.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the controller',
            'required' => true,
        ],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     */
    public function __construct(
        Console $io,
        protected Make $make,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to `App\Controllers`, and the generated class name is suffixed with `Controller`.
     *
     * @param string $name The controller name.
     * @param string|null $namespace The controller namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= 'App\Controllers';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Controller');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Controller file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('controller', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Controller file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
