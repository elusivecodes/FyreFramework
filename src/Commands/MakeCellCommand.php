<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Fyre\View\CellRegistry;
use Override;

use function file_exists;

/**
 * Implements the make cell console command.
 *
 * Generates a cell class using the `cell` stub.
 */
class MakeCellCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:cell';

    #[Override]
    protected string $description = 'Generate a new cell.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the cell',
            'required' => true,
        ],
        'method' => [
            'default' => 'display',
        ],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param CellRegistry $cellRegistry The CellRegistry.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected CellRegistry $cellRegistry,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see CellRegistry} namespace, or `App\Cells`.
     * The generated class name is suffixed with `Cell`.
     *
     * @param string $name The cell name.
     * @param string $method The cell method.
     * @param string|null $namespace The cell namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string $method, string|null $namespace = null): int|null
    {
        $namespace ??= $this->cellRegistry->getNamespaces()[0] ?? 'App\Cells';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Cell');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Cell file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('cell', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{method}' => $method,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Cell file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
