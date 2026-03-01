<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use Fyre\ORM\Attributes\Policy;
use Fyre\ORM\Model;
use Fyre\Utility\Inflector;
use ReflectionAttribute;
use ReflectionClass;

use function class_exists;
use function is_subclass_of;
use function preg_replace;

/**
 * Stores and resolves authorization policy mappings.
 */
class PolicyRegistry
{
    use DebugTrait;
    use NamespacesTrait;

    /**
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * @var array<string, class-string>
     */
    protected array $policyMap = [];

    /**
     * Constructs a PolicyRegistry.
     *
     * @param Container $container The Container.
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        protected Container $container,
        protected Inflector $inflector
    ) {}

    /**
     * Builds a policy instance for an alias.
     *
     * @param string $alias The policy alias.
     * @return object|null The policy instance or null if it cannot be resolved.
     */
    public function build(string $alias): mixed
    {
        $alias = $this->resolveAlias($alias);

        if (isset($this->policyMap[$alias])) {
            /** @var class-string $className */
            $className = $this->policyMap[$alias];

            return $this->container->build($className);
        }

        $singular = $this->inflector->singularize($alias);

        foreach ($this->namespaces as $namespace) {
            $fullClass = $namespace.$singular.'Policy';

            if (class_exists($fullClass)) {
                /** @var class-string $className */
                $className = $fullClass;

                return $this->container->build($className);
            }
        }

        return null;
    }

    /**
     * Clears all namespaces and policies.
     */
    public function clear(): void
    {
        $this->clearNamespaces();

        $this->aliases = [];
        $this->policyMap = [];
        $this->instances = [];
    }

    /**
     * Maps an alias to a policy class name.
     *
     * @param string $alias The policy alias.
     * @param class-string $className The policy class name.
     * @return static The PolicyRegistry instance.
     */
    public function map(string $alias, string $className): static
    {
        $alias = $this->resolveAlias($alias);

        $this->policyMap[$alias] = $className;

        return $this;
    }

    /**
     * Resolves a policy alias.
     *
     * Note: When the alias is a {@see Model} class name, the resolved alias is derived from a
     * {@see Policy} attribute (when present) or by reflecting the model alias/default naming.
     *
     * @param string $alias The policy alias.
     * @return string The resolved alias.
     */
    public function resolveAlias(string $alias): string
    {
        if (isset($this->aliases[$alias])) {
            return $this->aliases[$alias];
        }

        if (is_subclass_of($alias, Model::class)) {
            $reflection = new ReflectionClass($alias);

            $attribute = $reflection->getAttributes(Policy::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

            if ($attribute) {
                $policyInstance = $attribute->newInstance();
                $resolvedAlias = $policyInstance->getName();
            } else {
                $reflectProperty = $reflection->getProperty('alias');
                $resolvedAlias = $reflectProperty->hasDefaultValue() ?
                    $reflectProperty->getDefaultValue() :
                    (string) preg_replace('/Model$/', '', $reflection->getShortName());
            }
        } else {
            $resolvedAlias = $alias;
        }

        return $this->aliases[$alias] = $resolvedAlias;
    }

    /**
     * Unloads a policy.
     *
     * Note: Policies are cached under their resolved alias. Pass the resolved alias used by {@see self::use()}.
     *
     * @param string $alias The policy alias.
     * @return static The PolicyRegistry instance.
     */
    public function unload(string $alias): static
    {
        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Loads a shared policy instance.
     *
     * @param string $alias The alias.
     * @return object|null The policy instance or null if it cannot be resolved.
     */
    public function use(string $alias): mixed
    {
        $alias = $this->resolveAlias($alias);

        return $this->instances[$alias] ??= $this->build($alias);
    }
}
