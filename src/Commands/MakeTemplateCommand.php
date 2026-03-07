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
 * Implements the make template console command.
 *
 * Generates a template file using the `template` stub.
 */
class MakeTemplateCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:template';

    #[Override]
    protected string $description = 'Generate a new template.';

    #[Override]
    protected array $options = [
        'template' => [
            'text' => 'Please enter the template',
            'required' => true,
        ],
        'path' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param TemplateLocator $templateLocator The TemplateLocator.
     */
    public function __construct(
        Console $io,
        protected TemplateLocator $templateLocator,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The target file is written beneath the resolved template path.
     *
     * @param string $template The template name.
     * @param string|null $path The template path.
     * @return int|null The exit code.
     */
    public function run(string $template, string|null $path = null): int|null
    {
        $path ??= $this->templateLocator->getPaths()[0] ?? '';

        if (file_exists($path) && !is_dir($path)) {
            $this->io->error('Invalid template path.');

            return static::CODE_ERROR;
        }

        $template = Make::normalizePath($template);

        $fullPath = Path::join($path, $template.'.php');

        if (file_exists($fullPath)) {
            $this->io->error('Template file already exists.');

            return static::CODE_ERROR;
        }

        $contents = Make::loadStub('template');

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Template file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}
