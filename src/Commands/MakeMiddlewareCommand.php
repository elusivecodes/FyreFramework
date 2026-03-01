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
     * Runs the command.
     *
     * Note: The namespace defaults to `App\Middleware`, and the generated class name is suffixed with `Middleware`.
     *
     * @param Make $make The Make.
     * @param Console $io The Console.
     * @param string $name The middleware name.
     * @param string|null $namespace The middleware namespace.
     * @return int|null The exit code.
     */
    public function run(Make $make, Console $io, string $name, string|null $namespace = null): int|null
    {
        $namespace ??= 'App\Middleware';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Middleware');

        $path = $make->findPath($namespace);

        if (!$path) {
            $io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $io->error('Middleware file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('middleware', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Middleware file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
