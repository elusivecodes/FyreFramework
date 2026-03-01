<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Fixture;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use InvalidArgumentException;

use function is_subclass_of;
use function sprintf;

/**
 * Registers and resolves test fixtures.
 *
 * Fixture classes are located by searching configured namespaces and are cached per alias.
 */
class FixtureRegistry
{
    use DebugTrait;
    use NamespacesTrait;

    protected Container $container;

    /**
     * @var array<string, Fixture>
     */
    protected array $instances = [];

    /**
     * Constructs a FixtureRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Builds a Fixture.
     *
     * @param string $alias The fixture class alias.
     * @return Fixture The Fixture instance.
     *
     * @throws InvalidArgumentException If the fixture does not exist.
     */
    public function build(string $alias): Fixture
    {
        foreach ($this->namespaces as $namespace) {
            $className = $namespace.$alias.'Fixture';

            if (is_subclass_of($className, Fixture::class)) {
                /** @var class-string<Fixture> $className */
                return $this->container->build($className);
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Fixture `%s` does not exist.',
            $alias
        ));
    }

    /**
     * Clears all namespaces and fixtures.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->instances = [];
    }

    /**
     * Checks whether a fixture is loaded.
     *
     * @param string $alias The fixture alias.
     * @return bool Whether the fixture is loaded.
     */
    public function isLoaded(string $alias): bool
    {
        return isset($this->instances[$alias]);
    }

    /**
     * Unloads a fixture.
     *
     * @param string $alias The fixture alias.
     * @return static The FixtureRegistry instance.
     */
    public function unload(string $alias): static
    {
        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Load a shared Fixture instance.
     *
     * @param string $alias The fixture alias.
     * @return Fixture The Fixture.
     */
    public function use(string $alias): Fixture
    {
        return $this->instances[$alias] ??= $this->build($alias);
    }
}
