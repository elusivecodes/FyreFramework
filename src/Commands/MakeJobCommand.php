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
 * Implements the make job console command.
 *
 * Generates a job class using the `job` stub.
 */
class MakeJobCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:job';

    #[Override]
    protected string $description = 'Generate a new job.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the job',
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
     * Note: The namespace defaults to `App\Jobs`, and the generated class name is suffixed with `Job`.
     *
     * @param string $name The job name.
     * @param string|null $namespace The job namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= 'App\Jobs';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Job');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Job file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('job', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Job file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
