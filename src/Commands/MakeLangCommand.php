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
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Lang $lang The Lang.
     */
    public function __construct(
        Console $io,
        protected Lang $lang,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The locale defaults to the {@see Lang} default locale, and the base path defaults to the first configured lang path.
     *
     * @param string $file The file name.
     * @param string|null $language The language.
     * @param string|null $path The file path.
     * @return int|null The exit code.
     */
    public function run(string $file, string|null $language = null, string|null $path = null): int|null
    {
        $language ??= $this->lang->getDefaultLocale();
        $path ??= $this->lang->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $this->io->error('Invalid lang path.');

            return static::CODE_ERROR;
        }

        $file = Make::normalizePath($file);

        $fullPath = Path::join($path, $language, $file.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Lang file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('lang');

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Lang file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
