<?php
declare(strict_types=1);

namespace Fyre\View;

use BadMethodCallException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use Fyre\View\Helpers\CspHelper;
use Fyre\View\Helpers\FormatHelper;
use Fyre\View\Helpers\FormHelper;
use Fyre\View\Helpers\UrlHelper;
use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

use function array_merge;
use function array_pop;
use function count;
use function explode;
use function extract;
use function func_get_arg;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function sprintf;

/**
 * Renders templates and layouts with helper and cell support.
 *
 * Note: Rendering uses output buffering and {@see extract()} to inject view data into
 * templates. Event listeners can override rendered content via `View.afterRender` and
 * `View.afterLayout`.
 *
 * @property CspHelper $Csp
 * @property FormatHelper $Format
 * @property FormHelper $Form
 * @property UrlHelper $Url
 */
class View
{
    use DebugTrait;
    use EventDispatcherTrait;
    use MacroTrait;

    /**
     * @var array<string, string>
     */
    protected array $blocks = [];

    /**
     * @var array<string, mixed>[]
     */
    protected array $blockStack = [];

    protected string $content = '';

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<string, Helper>
     */
    protected array $helpers = [];

    /**
     * Constructs a View.
     *
     * @param TemplateLocator $templateLocator The TemplateLocator.
     * @param HelperRegistry $helperRegistry The HelperRegistry.
     * @param CellRegistry $cellRegistry The CellRegistry.
     * @param EventManager $eventManager The EventManager.
     * @param ServerRequestInterface $request The ServerRequest.
     */
    public function __construct(
        protected TemplateLocator $templateLocator,
        protected HelperRegistry $helperRegistry,
        protected CellRegistry $cellRegistry,
        protected EventManager $eventManager,
        protected ServerRequestInterface $request,
        protected string|null $layout = 'default'
    ) {}

    /**
     * Loads a helper.
     *
     * @param string $name The helper name.
     * @return Helper The Helper instance.
     */
    public function __get(string $name): Helper
    {
        $this->loadHelper($name);

        return $this->helpers[$name];
    }

    /**
     * Appends content to a block.
     *
     * @param string $name The block name.
     * @return static The View instance.
     */
    public function append(string $name): static
    {
        return $this->start($name, 'append');
    }

    /**
     * Assigns content to a block.
     *
     * @param string $name The block name.
     * @param string $content The block content.
     * @return static The View instance.
     */
    public function assign(string $name, string $content): static
    {
        $this->blocks[$name] = $content;

        return $this;
    }

    /**
     * Renders a cell.
     *
     * @param string $cell The cell name.
     * @param array<mixed> $args The cell method arguments.
     * @return Cell The new Cell instance.
     */
    public function cell(string $cell, array $args = []): Cell
    {
        $parts = explode('::', $cell, 2);

        if (count($parts) === 2) {
            [$cell, $action] = $parts;
        } else {
            $action = null;
        }

        return $this->cellRegistry->build($cell, $this, ['action' => $action, 'args' => $args]);
    }

    /**
     * Returns the layout content.
     *
     * @return string The layout content.
     */
    public function content(): string
    {
        return $this->content;
    }

    /**
     * Renders an element.
     *
     * @param string $file The element file.
     * @param array<string, mixed> $data The element data.
     * @return string The rendered element.
     *
     * @throws InvalidArgumentException If the element does not exist.
     */
    public function element(string $file, array $data = []): string
    {
        $filePath = $this->templateLocator->locate($file, TemplateLocator::ELEMENTS_FOLDER);

        if (!$filePath) {
            throw new InvalidArgumentException(sprintf(
                'Element template `%s` could not be found.',
                $file
            ));
        }

        $this->dispatchEvent('View.beforeElement', ['filePath' => $filePath]);

        $content = $this->evaluate($filePath, $data);

        $event = $this->dispatchEvent('View.afterElement', ['filePath' => $filePath, 'content' => $content]);

        $result = $event->getResult();

        if ($result !== null) {
            return $result;
        }

        return $content;
    }

    /**
     * Ends a block.
     *
     * Note: Blocks are implemented using output buffering; the active buffer is captured
     * and then closed when ending a block.
     *
     * @return static The View instance.
     *
     * @throws BadMethodCallException If a block is not opened.
     */
    public function end(): static
    {
        $block = array_pop($this->blockStack);

        if (!$block) {
            throw new BadMethodCallException('Unable to close block while no blocks are opened.');
        }

        $contents = (string) ob_get_contents();

        ob_end_clean();

        $name = $block['name'];

        $this->blocks[$name] ??= '';

        switch ($block['type']) {
            case 'append':
                $this->blocks[$name] .= $contents;
                break;
            case 'prepend':
                $this->blocks[$name] = $contents.$this->blocks[$name];
                break;
            default:
                $this->blocks[$name] = $contents;
                break;
        }

        return $this;
    }

    /**
     * Fetches a block.
     *
     * @param string $name The block name.
     * @param string $default The default value.
     * @return string The block contents.
     */
    public function fetch(string $name, string $default = ''): string
    {
        return $this->blocks[$name] ?? $default;
    }

    /**
     * Returns the view data.
     *
     * @return array<string, mixed> The view data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the layout.
     *
     * @return string|null The layout.
     */
    public function getLayout(): string|null
    {
        return $this->layout;
    }

    /**
     * Returns the ServerRequest.
     *
     * @return ServerRequestInterface The ServerRequest instance.
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Loads a Helper.
     *
     * @param string $name The helper name.
     * @param array<string, mixed> $options The helper options.
     * @return static The View instance.
     */
    public function loadHelper(string $name, array $options = []): static
    {
        $this->helpers[$name] ??= $this->helperRegistry->build($name, $this, $options);

        return $this;
    }

    /**
     * Prepends content to a block.
     *
     * @param string $name The block name.
     * @return static The View instance.
     */
    public function prepend(string $name): static
    {
        return $this->start($name, 'prepend');
    }

    /**
     * Renders a template.
     *
     * Note: Any unclosed blocks are automatically ended after layout rendering. If blocks
     * were left open, a {@see LogicException} is thrown to signal template misuse.
     *
     * @param string $file The template file.
     * @return string The rendered template.
     *
     * @throws LogicException If there are unclosed blocks.
     * @throws InvalidArgumentException If the template file does not exist.
     */
    public function render(string $file): string
    {
        $filePath = $this->templateLocator->locate($file);

        if (!$filePath) {
            throw new InvalidArgumentException(sprintf(
                'Template `%s` could not be found.',
                $file
            ));
        }

        $layoutPath = $this->layout ?
            $this->templateLocator->locate($this->layout, TemplateLocator::LAYOUTS_FOLDER) :
            null;

        if ($this->layout && !$layoutPath) {
            throw new InvalidArgumentException(sprintf(
                'Layout template `%s` could not be found.',
                $this->layout
            ));
        }

        $this->dispatchEvent('View.beforeRender', ['filePath' => $filePath]);

        $this->content = $this->evaluate($filePath, $this->data);

        $event = $this->dispatchEvent('View.afterRender', ['filePath' => $filePath, 'content' => $this->content]);

        $result = $event->getResult();

        if ($result !== null) {
            $this->content = $result;
        }

        if (!$layoutPath) {
            $result = $this->content;
        } else {
            $this->dispatchEvent('View.beforeLayout', ['layoutPath' => $layoutPath]);

            $result = $this->evaluate($layoutPath, $this->data);

            $layoutEvent = $this->dispatchEvent('View.afterLayout', ['layoutPath' => $layoutPath, 'content' => $result]);

            $result = $layoutEvent->getResult() ?? $result;
        }

        $hasUnclosedBlocks = $this->blockStack !== [];

        while ($this->blockStack !== []) {
            $this->end();
        }

        if ($hasUnclosedBlocks) {
            throw new LogicException('Unable to render view while blocks remain open.');
        }

        $this->blocks = [];

        return $result;
    }

    /**
     * Resets content of a block.
     *
     * @param string $name The block name.
     * @return static The View.
     */
    public function reset(string $name): static
    {
        return $this->assign($name, '');
    }

    /**
     * Sets a view data value.
     *
     * @param string $name The data name.
     * @param mixed $value The data value.
     * @return static The View.
     */
    public function set(string $name, mixed $value): static
    {
        $this->data[$name] = $value;

        return $this;
    }

    /**
     * Sets view data.
     *
     * @param array<string, mixed> $data The view data.
     * @return static The View.
     */
    public function setData(array $data): static
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Sets the layout.
     *
     * @param string|null $layout The layout.
     * @return static The View.
     */
    public function setLayout(string|null $layout): static
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Starts content for a block.
     *
     * @param string $name The block name.
     * @param string|null $type The block type.
     * @return static The View.
     */
    public function start(string $name, string|null $type = null): static
    {
        ob_start();

        $this->blockStack[] = [
            'name' => $name,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * Renders and injects data into a file.
     *
     * Note: This uses {@see extract()} to create local variables from `$data`. Variable
     * names may collide with local scope.
     *
     * @param string $filePath The file path.
     * @param array<string, mixed> $data The data to inject.
     * @return string The rendered file.
     */
    protected function evaluate(string $filePath, array $data): string
    {
        extract($data);

        try {
            ob_start();

            include func_get_arg(0);

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
