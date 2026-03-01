<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Lang;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Override;

use function file_exists;
use function is_dir;

/**
 * Implements the make lang console command.
 *
 * Generates a language file using the `lang` stub.
 */
class MakeLangCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:lang';

    #[Override]
    protected string $description = 'Generate a new language file.';

    #[Override]
    protected array $options = [
        'file' => [
            'text' => 'Please enter the language file',
            'required' => true,
        ],
        'language' => [],
        'path' => [],
    ];

    /**
     * Runs the command.
     *
     * Note: The locale defaults to the {@see Lang} default locale, and the base path defaults to the first configured lang path.
     *
     * @param Lang $lang The Lang.
     * @param Console $io The Console.
     * @param string $file The file name.
     * @param string|null $language The language.
     * @param string|null $path The file path.
     * @return int|null The exit code.
     */
    public function run(Lang $lang, Console $io, string $file, string|null $language = null, string|null $path = null): int|null
    {
        $language ??= $lang->getDefaultLocale();
        $path ??= $lang->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $io->error('Invalid lang path.');

            return static::CODE_ERROR;
        }

        $file = Make::normalizePath($file);

        $fullPath = Path::join($path, $language, $file.'.php');

        if (file_exists($fullPath)) {
            $io->error('Lang file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('lang');

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Lang file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
