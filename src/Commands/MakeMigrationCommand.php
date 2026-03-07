<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\DB\Migration\MigrationRunner;
use Fyre\Utility\Path;
use Override;

use function date;
use function file_exists;

/**
 * Implements the make migration console command.
 *
 * Generates a migration class using the `migration` stub.
 */
class MakeMigrationCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:migration';

    #[Override]
    protected string $description = 'Generate a new migration.';

    #[Override]
    protected array $options = [
        'name' => [
            'required' => true,
        ],
        'version' => [],
        'namespace' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param MigrationRunner $migrationRunner The MigrationRunner.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected MigrationRunner $migrationRunner,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: When no version is supplied, the version is generated as `YmdHis`. The namespace defaults to the
     * first registered {@see MigrationRunner} namespace, or `App\Migrations`.
     *
     * @param string $name The migration name.
     * @param string|null $version The migration version.
     * @param string|null $namespace The migration namespace.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $version = null, string|null $namespace = null): int|null
    {
        $version ??= date('YmdHis');
        $namespace ??= $this->migrationRunner->getNamespaces()[0] ?? 'App\Migrations';

        $migration = 'Migration_'.$version.'_'.$name;

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $migration);

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Migration file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('migration', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Migration file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
