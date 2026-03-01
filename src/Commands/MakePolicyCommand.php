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
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see PolicyRegistry} namespace, or `App\Policies`.
     * The generated class name is suffixed with `Policy`.
     *
     * @param Make $make The Make.
     * @param PolicyRegistry $policyRegistry The PolicyRegistry.
     * @param Console $io The Console.
     * @param string $name The policy name.
     * @param string|null $namespace The policy namespace.
     * @return int|null The exit code.
     */
    public function run(Make $make, PolicyRegistry $policyRegistry, Console $io, string $name, string|null $namespace = null): int|null
    {
        $namespace ??= $policyRegistry->getNamespaces()[0] ?? 'App\Policies';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Policy');

        $path = $make->findPath($namespace);

        if (!$path) {
            $io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $io->error('Policy file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('policy', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Policy file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
