<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Core\Make\FixtureSourceBuilder;
use Fyre\Core\Make\GeneratedFile;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\Utility\Path;
use Override;

/**
 * Implements the make fixture console command.
 *
 * Generates a fixture class using the `fixture` stub.
 */
class MakeFixtureCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:fixture';

    #[Override]
    protected string $description = 'Generate a new fixture.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the fixture',
            'required' => true,
        ],
        'namespace' => [],
        'force' => [
            'as' => 'boolean',
            'default' => false,
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param FixtureRegistry $fixtureRegistry The FixtureRegistry.
     * @param FixtureSourceBuilder $sourceBuilder The fixture source builder.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected FixtureRegistry $fixtureRegistry,
        protected FixtureSourceBuilder $sourceBuilder,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to the first registered {@see FixtureRegistry} namespace, or `Tests\Fixtures`.
     * The generated class name is suffixed with `Fixture`.
     *
     * @param string $name The fixture name.
     * @param string|null $namespace The fixture namespace.
     * @param bool $force Whether to overwrite an existing file.
     * @return int|null The exit code.
     */
    public function run(string $name, string|null $namespace = null, bool $force = false): int|null
    {
        $namespace ??= $this->fixtureRegistry->getNamespaces()[0] ?? 'Tests\Fixtures';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Fixture');

        $contents = $this->sourceBuilder->build($namespace, $className);
        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $generatedFile = new GeneratedFile(
            Path::join($path, $className.'.php'),
            $contents
        );

        if (!$generatedFile->isValid($force)) {
            $this->io->error('Fixture file already exists.');

            return static::CODE_ERROR;
        }

        if (!$generatedFile->save()) {
            $this->io->error('Fixture file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
