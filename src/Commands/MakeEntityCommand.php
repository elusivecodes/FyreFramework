<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\ORM\EntityLocator;
use Fyre\Utility\Path;
use Override;

use function file_exists;

/**
 * Implements the make entity console command.
 *
 * Generates an entity class using the `entity` stub.
 */
class MakeEntityCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:entity';

    #[Override]
    protected string $description = 'Generate a new entity.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the entity',
            'required' => true,
        ],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param EntityLocator $entityLocator The EntityLocator.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected EntityLocator $entityLocator,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see EntityLocator} namespace, or `App\Entities`.
     *
     * @param string $name The entity name.
     * @param string|null $namespace The entity namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= $this->entityLocator->getNamespaces()[0] ?? 'App\Entities';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name);

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Entity file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('entity', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Entity file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
