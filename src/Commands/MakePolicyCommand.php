<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Auth\PolicyRegistry;
use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Override;

use function file_exists;

/**
 * Implements the make policy console command.
 *
 * Generates a policy class using the `policy` stub.
 */
class MakePolicyCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:policy';

    #[Override]
    protected string $description = 'Generate a new policy.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the policy',
            'required' => true,
        ],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param PolicyRegistry $policyRegistry The PolicyRegistry.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected PolicyRegistry $policyRegistry,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see PolicyRegistry} namespace, or `App\Policies`.
     * The generated class name is suffixed with `Policy`.
     *
     * @param string $name The policy name.
     * @param string|null $namespace The policy namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null): int|null
    {
        $namespace ??= $this->policyRegistry->getNamespaces()[0] ?? 'App\Policies';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Policy');

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Policy file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('policy', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Policy file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
