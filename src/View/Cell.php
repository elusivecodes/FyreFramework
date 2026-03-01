<?php
declare(strict_types=1);

namespace Fyre\View;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use ReflectionClass;
use RuntimeException;

use function method_exists;
use function preg_replace;
use function sprintf;

/**
 * Provides a base class for view cells.
 *
 * Cells execute an action method and then render a template using a child {@see View}
 * instance (without a layout).
 */
abstract class Cell
{
    use DebugTrait;
    use MacroTrait;

    protected string $action;

    /**
     * @var array<mixed>
     */
    protected array $args;

    protected string|null $template = null;

    protected View $view;

    /**
     * Constructs a Cell.
     *
     * @param Container $container The Container.
     * @param TemplateLocator $templateLocator The TemplateLocator.
     * @param View $parentView The parent View.
     * @param array<string, mixed> $options The cell options.
     */
    public function __construct(
        protected Container $container,
        protected TemplateLocator $templateLocator,
        protected View $parentView,
        array $options = []
    ) {
        $this->action = $options['action'] ?? 'display';
        $this->args = $options['args'] ?? [];
    }

    /**
     * Renders the cell as a string.
     *
     * @return string The rendered cell.
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Returns the template.
     *
     * @return string|null The template.
     */
    public function getTemplate(): string|null
    {
        return $this->template;
    }

    /**
     * Returns the View.
     *
     * @return View The View instance.
     */
    public function getView(): View
    {
        return $this->view ??= $this->container->build(View::class, [
            'request' => $this->parentView->getRequest(),
            'layout' => null,
        ]);
    }

    /**
     * Renders the cell.
     *
     * Note: This dispatches `Cell.beforeAction/afterAction` around the action method call,
     * determines a default template based on the action name when none is set, and renders
     * using a child {@see View} with no layout.
     *
     * @return string The rendered cell.
     *
     * @throws RuntimeException If the method or template does not exist.
     */
    public function render(): string
    {
        $cell = (string) preg_replace('/Cell$/', '', new ReflectionClass(static::class)->getShortName());

        if (!method_exists($this, $this->action)) {
            throw new RuntimeException(sprintf(
                'Cell method `%s::%s` does not exist.',
                $cell,
                $this->action
            ));
        }

        $this->parentView->dispatchEvent('Cell.beforeAction', ['cell' => $this, 'action' => $this->action, 'args' => $this->args]);

        $this->container->call([$this, $this->action], $this->args);

        $this->parentView->dispatchEvent('Cell.afterAction', ['cell' => $this, 'action' => $this->action, 'args' => $this->args]);

        $template = $this->template;

        if ($template === null) {
            $file = TemplateLocator::normalize($this->action);
            $template = Path::join($cell, $file);
        }

        $filePath = $this->templateLocator->locate($template, TemplateLocator::CELLS_FOLDER);

        if (!$filePath) {
            throw new RuntimeException(sprintf(
                'Cell template `%s` could not be found.',
                $template
            ));
        }

        $this->parentView->dispatchEvent('Cell.beforeRender', ['filePath' => $filePath]);

        $content = Path::join(TemplateLocator::CELLS_FOLDER, $template) |> $this->getView()->render(...);

        $event = $this->parentView->dispatchEvent('Cell.afterRender', ['filePath' => $filePath, 'content' => $content]);

        $result = $event->getResult();

        if ($result !== null) {
            return $result;
        }

        return $content;
    }

    /**
     * Sets a view data value.
     *
     * @param string $name The data name.
     * @param mixed $value The data value.
     * @return static The Cell.
     */
    public function set(string $name, mixed $value): static
    {
        $this->getView()->set($name, $value);

        return $this;
    }

    /**
     * Sets view data.
     *
     * @param array<string, mixed> $data The view data.
     * @return static The Cell.
     */
    public function setData(array $data): static
    {
        $this->getView()->setData($data);

        return $this;
    }

    /**
     * Sets the template file.
     *
     * @param string $file The template file.
     * @return static The Cell.
     */
    public function setTemplate(string $file): static
    {
        $this->template = $file;

        return $this;
    }
}
