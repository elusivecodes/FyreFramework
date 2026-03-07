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
 * Implements the make middleware console command.
 *
 * Generates a middleware class using the `middleware` stub.
 */
class MakeMiddlewareCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:middleware';

    #[Override]
    protected string $description = 'Generate a new middleware.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the middleware',
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
     * Note: The namespace defaults to `App\Middleware`, and the generated class name is suffixed with `Middleware`.
     *
     * @param string $name The middleware name.
     * @param string|null $namespace The middleware namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= 'App\Middleware';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Middleware');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Middleware file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('middleware', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Middleware file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
