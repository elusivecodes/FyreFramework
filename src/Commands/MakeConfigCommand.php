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
     * Runs the command.
     *
     * Note: The path defaults to the first configured {@see Config} path.
     *
     * @param Config $config The Config.
     * @param Console $io The Console.
     * @param string $file The file name.
     * @param string|null $path The file path.
     * @return int|null The exit code.
     */
    public function run(Config $config, Console $io, string $file, string|null $path = null): int|null
    {
        $path ??= $config->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $io->error('Invalid config path.');

            return static::CODE_ERROR;
        }

        $file = Make::normalizePath($file);

        $fullPath = Path::join($path, $file.'.php');

        if (file_exists($fullPath)) {
            $io->error('Config file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('config');

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Config file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
