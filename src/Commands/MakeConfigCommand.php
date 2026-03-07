<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Override;

use function file_exists;
use function is_dir;

/**
 * Implements the make config console command.
 *
 * Generates a config file using the `config` stub.
 */
class MakeConfigCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:config';

    #[Override]
    protected string $description = 'Generate a new config file.';

    #[Override]
    protected array $options = [
        'file' => [
            'text' => 'Please enter the config file',
            'required' => true,
        ],
        'path' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Config $config The Config.
     */
    public function __construct(
        Console $io,
        protected Config $config,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The path defaults to the first configured {@see Config} path.
     *
     * @param string $file The file name.
     * @param string|null $path The file path.
     * @return int|null The exit code.
     */
    public function run(string $file, string|null $path = null): int|null
    {
        $path ??= $this->config->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $this->io->error('Invalid config path.');

            return static::CODE_ERROR;
        }

        $file = Make::normalizePath($file);

        $fullPath = Path::join($path, $file.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Config file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('config');

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Config file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
