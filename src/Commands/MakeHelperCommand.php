<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Fyre\View\HelperRegistry;
use Override;

use function file_exists;

/**
 * Implements the make helper console command.
 *
 * Generates a helper class using the `helper` stub.
 */
class MakeHelperCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:helper';

    #[Override]
    protected string $description = 'Generate a new helper.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the helper',
            'required' => true,
        ],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param HelperRegistry $helperRegistry The HelperRegistry.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected HelperRegistry $helperRegistry,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see HelperRegistry} namespace, or `App\Helpers`.
     * The generated class name is suffixed with `Helper`.
     *
     * @param string $name The helper name.
     * @param string|null $namespace The helper namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= $this->helperRegistry->getNamespaces()[0] ?? 'App\Helpers';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Helper');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Helper file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('helper', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Helper file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
