<?php
declare(strict_types=1);

namespace Fyre\ORM;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use Fyre\Utility\Inflector;
use ReflectionClass;

use function array_search;
use function assert;
use function class_exists;
use function is_subclass_of;

/**
 * Resolves entity classes for model aliases.
 *
 * Located entity classes are cached per alias. When no matching class is found, the
 * default entity class is returned.
 */
class EntityLocator
{
    use DebugTrait;
    use NamespacesTrait;

    /**
     * @var class-string<Entity>
     */
    protected string $defaultEntityClass = Entity::class;

    /**
     * @var array<string, class-string<Entity>>
     */
    protected array $entities = [];

    /**
     * Constructs an EntityLocator.
     *
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        protected Inflector $inflector
    ) {}

    /**
     * Clears all namespaces and entities.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->entities = [];
    }

    /**
     * Finds the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return class-string<Entity> The entity class name.
     */
    public function find(string $alias): string
    {
        return $this->entities[$alias] ??= static::locate($alias);
    }

    /**
     * Finds the alias for an entity class.
     *
     * @param class-string<Entity> $entityClass The entity class name.
     * @return string The alias.
     */
    public function findAlias(string $entityClass): string
    {
        $alias = array_search($entityClass, $this->entities, true);

        if ($alias) {
            return (string) $alias;
        }

        assert(class_exists($entityClass));

        return new ReflectionClass($entityClass)->getShortName() |> $this->inflector->pluralize(...);
    }

    /**
     * Returns the default entity class name.
     *
     * @return class-string<Entity> The default entity class name.
     */
    public function getDefaultEntityClass(): string
    {
        return $this->defaultEntityClass;
    }

    /**
     * Maps an alias to an entity class.
     *
     * @param string $alias The alias.
     * @param class-string<Entity> $entityClass The entity class.
     * @return static The EntityLocator.
     */
    public function map(string $alias, string $entityClass): static
    {
        $this->entities[$alias] = $entityClass;

        return $this;
    }

    /**
     * Sets the default entity class name.
     *
     * @param class-string<Entity> $defaultEntityClass The default entity class name.
     * @return static The EntityLocator.
     */
    public function setDefaultEntityClass(string $defaultEntityClass): static
    {
        $this->defaultEntityClass = $defaultEntityClass;

        return $this;
    }

    /**
     * Locates the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return class-string<Entity> The entity class name.
     */
    protected function locate(string $alias): string
    {
        $alias = $this->inflector->classify($alias);

        foreach ($this->namespaces as $namespace) {
            $fullClass = $namespace.$alias;

            if (is_subclass_of($fullClass, Entity::class)) {
                return $fullClass;
            }
        }

        return $this->defaultEntityClass;
    }
}
