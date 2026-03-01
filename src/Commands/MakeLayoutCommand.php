<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Path;
use Fyre\View\TemplateLocator;
use Override;

use function file_exists;
use function is_dir;

/**
 * Implements the make layout console command.
 *
 * Generates a layout template file using the `layout` stub.
 */
class MakeLayoutCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:layout';

    #[Override]
    protected string $description = 'Generate a new layout.';

    #[Override]
    protected array $options = [
        'template' => [
            'text' => 'Please enter the layout template',
            'required' => true,
        ],
        'path' => [],
    ];

    /**
     * Runs the command.
     *
     * Note: The target file is written beneath the resolved template path in the {@see TemplateLocator::LAYOUTS_FOLDER} folder.
     *
     * @param TemplateLocator $templateLocator The TemplateLocator.
     * @param Console $io The Console.
     * @param string $template The template name.
     * @param string|null $path The template path.
     * @return int|null The exit code.
     */
    public function run(TemplateLocator $templateLocator, Console $io, string $template, string|null $path = null): int|null
    {
        $path ??= $templateLocator->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $io->error('Invalid layout path.');

            return static::CODE_ERROR;
        }

        $template = Make::normalizePath($template);

        $fullPath = Path::join($path, TemplateLocator::LAYOUTS_FOLDER, $template.'.php');

        if (file_exists($fullPath)) {
            $io->error('Layout file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('layout');

        if (!Make::saveFile($fullPath, $contents)) {
            $io->error('Layout file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
