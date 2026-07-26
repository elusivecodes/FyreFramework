<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Core\Make\GeneratedFile;
use Fyre\Core\Make\TestSourceBuilder;
use Fyre\Utility\Path;
use Override;

/**
 * Implements the make test console command.
 *
 * Generates a test case class using the `test` stub.
 */
class MakeTestCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:test';

    #[Override]
    protected string $description = 'Generate a new test case.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the test',
            'required' => true,
        ],
        'namespace' => [],
        'fixture' => [],
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
     * @param TestSourceBuilder $sourceBuilder The test source builder.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected TestSourceBuilder $sourceBuilder,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to `Tests\TestCase`, and the generated class name is suffixed with `Test`.
     *
     * @param string $name The test name.
     * @param string|null $namespace The test namespace.
     * @param string|null $fixture The fixture alias.
     * @param bool $force Whether to overwrite an existing file.
     * @return int|null The exit code.
     */
    public function run(
        string $name,
        string|null $namespace = null,
        string|null $fixture = null,
        bool $force = false
    ): int|null {
        $namespace ??= 'Tests\TestCase';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name.'Test');

        $contents = $this->sourceBuilder->build($namespace, $className, $fixture);
        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Test file namespace path not found.');

            return static::CODE_ERROR;
        }

        $generatedFile = new GeneratedFile(
            Path::join($path, $className.'.php'),
            $contents
        );

        if (!$generatedFile->isValid($force)) {
            $this->io->error('Test file already exists.');

            return static::CODE_ERROR;
        }

        if (!$generatedFile->save()) {
            $this->io->error('Test file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
