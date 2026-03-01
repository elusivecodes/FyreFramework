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
 * Resolves and caches view helper instances.
 *
 * Helper classes are located by searching configured namespaces, with a fallback to the
 * built-in helpers namespace. Misses are cached to avoid repeated lookups.
 */
class HelperRegistry
{
    use DebugTrait;
    use NamespacesTrait;

    /**
     * @var array<string, class-string<Helper>|null>
     */
    protected array $helpers = [];

    /**
     * Constructs a HelperRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Builds a helper.
     *
     * @param string $name The helper name.
     * @param View $view The View.
     * @param array<string, mixed> $options The helper options.
     * @return Helper The Helper instance.
     *
     * @throws InvalidArgumentException If the helper is not valid.
     */
    public function build(string $name, View $view, array $options = []): Helper
    {
        $className = $this->find($name);

        if ($className === null) {
            throw new InvalidArgumentException(sprintf(
                'Helper `%s` could not be found.',
                $name
            ));
        }

        return $this->container->build($className, ['view' => $view, 'options' => $options]);
    }

    /**
     * Clears all namespaces and helpers.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->helpers = [];
    }

    /**
     * Finds a helper class.
     *
     * @param string $name The helper name.
     * @return class-string<Helper>|null The helper class.
     */
    public function find(string $name): string|null
    {
        if (array_key_exists($name, $this->helpers)) {
            return $this->helpers[$name];
        }

        return $this->helpers[$name] = $this->locate($name);
    }

    /**
     * Locates a helper class.
     *
     * @param string $name The helper name.
     * @return class-string<Helper>|null The helper class.
     */
    protected function locate(string $name): string|null
    {
        $namespaces = array_merge($this->namespaces, ['\Fyre\View\Helpers\\']);

        foreach ($namespaces as $namespace) {
            $className = $namespace.$name.'Helper';

            if (is_subclass_of($className, Helper::class)) {
                return $className;
            }
        }

        return null;
    }
}
