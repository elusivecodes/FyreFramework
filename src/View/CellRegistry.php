<?php
declare(strict_types=1);

namespace Fyre\View;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use InvalidArgumentException;

use function array_key_exists;
use function array_merge;
use function is_subclass_of;
use function sprintf;

/**
 * Resolves and caches view cell instances.
 *
 * Cell classes are located by searching configured namespaces, with a fallback to the
 * built-in cells namespace. Misses are cached to avoid repeated lookups.
 */
class CellRegistry
{
    use DebugTrait;
    use NamespacesTrait;

    /**
     * @var array<string, class-string<Cell>|null>
     */
    protected array $cells = [];

    /**
     * Constructs a CellRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Builds a cell.
     *
     * @param string $name The cell name.
     * @param View $view The View.
     * @param array<string, mixed> $options The cell options.
     * @return Cell The Cell instance.
     *
     * @throws InvalidArgumentException If the cell is not valid.
     */
    public function build(string $name, View $view, array $options = []): Cell
    {
        $className = $this->find($name);

        if ($className === null) {
            throw new InvalidArgumentException(sprintf(
                'Cell `%s` could not be found.',
                $name
            ));
        }

        return $this->container->build($className, ['parentView' => $view, 'options' => $options]);
    }

    /**
     * Clears all namespaces and cells.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->cells = [];
    }

    /**
     * Finds a cell class.
     *
     * @param string $name The cell name.
     * @return class-string<Cell>|null The cell class.
     */
    public function find(string $name): string|null
    {
        if (array_key_exists($name, $this->cells)) {
            return $this->cells[$name];
        }

        return $this->cells[$name] = $this->locate($name);
    }

    /**
     * Locates a cell class.
     *
     * @param string $name The cell name.
     * @return class-string<Cell>|null The cell class.
     */
    protected function locate(string $name): string|null
    {
        $namespaces = array_merge($this->namespaces, ['\Fyre\View\Cells\\']);

        foreach ($namespaces as $namespace) {
            $className = $namespace.$name.'Cell';

            if (is_subclass_of($className, Cell::class)) {
                return $className;
            }
        }

        return null;
    }
}
