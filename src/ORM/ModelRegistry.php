<?php
declare(strict_types=1);

namespace Fyre\ORM;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use RuntimeException;

use function is_subclass_of;
use function sprintf;

/**
 * Resolves and caches model instances.
 *
 * Models are located by searching configured namespaces for a `<ClassAlias>Model` class,
 * falling back to the default model class when none is found.
 */
class ModelRegistry
{
    use DebugTrait;
    use NamespacesTrait;

    /**
     * @var class-string<Model>
     */
    protected string $defaultModelClass = Model::class;

    /**
     * @var array<string, Model>
     */
    protected array $instances = [];

    /**
     * Constructs a ModelRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Builds a Model.
     *
     * @param string $classAlias The model class alias.
     * @return Model The Model.
     */
    public function build(string $classAlias): Model
    {
        foreach ($this->namespaces as $namespace) {
            $fullClass = $namespace.$classAlias.'Model';

            if (is_subclass_of($fullClass, Model::class)) {
                /** @var class-string<Model> $className */
                $className = $fullClass;

                return $this->container->build($className);
            }
        }

        return $this->createDefaultModel()->setClassAlias($classAlias);
    }

    /**
     * Clears all namespaces and models.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->instances = [];
    }

    /**
     * Creates a default Model.
     *
     * @return Model The Model instance.
     */
    public function createDefaultModel(): Model
    {
        return $this->container->build($this->defaultModelClass);
    }

    /**
     * Returns the default model class name.
     *
     * @return class-string<Model> The default model class name.
     */
    public function getDefaultModelClass(): string
    {
        return $this->defaultModelClass;
    }

    /**
     * Checks whether a model is loaded.
     *
     * @param string $alias The model alias.
     * @return bool Whether the model is loaded.
     */
    public function isLoaded(string $alias): bool
    {
        return isset($this->instances[$alias]);
    }

    /**
     * Sets the default model class name.
     *
     * @param class-string<Model> $defaultModelClass The default model class name.
     * @return static The ModelRegistry instance.
     */
    public function setDefaultModelClass(string $defaultModelClass): static
    {
        $this->defaultModelClass = $defaultModelClass;

        return $this;
    }

    /**
     * Unloads a model.
     *
     * @param string $alias The model alias.
     * @return static The ModelRegistry.
     */
    public function unload(string $alias): static
    {
        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Loads a shared Model instance.
     *
     * @param string $alias The model alias.
     * @param string|null $classAlias The model class alias.
     * @return Model The Model instance.
     *
     * @throws RuntimeException If the alias is already used by a different class.
     */
    public function use(string $alias, string|null $classAlias = null): Model
    {
        if (!isset($this->instances[$alias])) {
            $this->instances[$alias] = $classAlias && $classAlias !== $alias ?
                $this->build($classAlias)->setAlias($alias) :
                $this->build($alias);
        } else if ($classAlias && $this->instances[$alias]->getClassAlias() !== $classAlias) {
            throw new RuntimeException(sprintf(
                'Model alias `%s` is already used by another class.',
                $alias
            ));
        }

        return $this->instances[$alias];
    }
}
