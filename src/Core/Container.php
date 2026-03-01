<?php
declare(strict_types=1);

namespace Fyre\Core;

use Closure;
use Fyre\Core\Exceptions\ContainerException;
use Fyre\Core\Exceptions\ContainerNotFoundException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Override;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function array_key_exists;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_search;
use function array_slice;
use function array_values;
use function assert;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function method_exists;
use function sprintf;
use function str_contains;

/**
 * Provides a dependency injection container.
 */
class Container implements ContainerInterface
{
    use DebugTrait;
    use MacroTrait;

    protected static Container|null $instance = null;

    /**
     * @var string[]
     */
    protected array $aliasStack = [];

    /**
     * @var array<string, array{0: Closure|string, 1: bool}>
     */
    protected array $bindings = [];

    /**
     * @var class-string[]
     */
    protected array $buildStack = [];

    /**
     * @var array<class-string<ContextualAttribute<mixed>>, Closure>
     */
    protected array $contextualAttributes = [];

    /**
     * @var array<string, array<string, true>>
     */
    protected array $dependencyMap = [];

    /**
     * @var object[][]
     */
    protected array $dependencyStack = [];

    /**
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * @var array<string, true>
     */
    protected array $scoped = [];

    /**
     * Returns the global instance.
     *
     * @return Container The Container instance.
     */
    public static function getInstance(): Container
    {
        return static::$instance ??= new static();
    }

    /**
     * Sets the global instance.
     *
     * @param Container $instance The Container.
     */
    public static function setInstance(Container $instance): void
    {
        static::$instance = $instance;
    }

    /**
     * Constructs a Container.
     *
     * @param bool $bind Whether to bind the instance to itself.
     */
    public function __construct(bool $bind = true)
    {
        if ($bind) {
            $this->instance(self::class, $this);
        }
    }

    /**
     * Binds an alias to a factory Closure or class name.
     *
     * @param string $alias The alias.
     * @param Closure|string|null $factory The factory Closure or class name.
     * @param bool $shared Whether the instance of this alias should be shared.
     * @param bool $scoped Whether the instance of this alias is scoped.
     * @return static The Container instance.
     */
    public function bind(string $alias, Closure|string|null $factory = null, bool $shared = false, bool $scoped = false): static
    {
        $this->unset($alias);
        $this->unscoped($alias);

        $factory ??= $alias;

        $this->bindings[$alias] = [$factory, $shared];

        if ($scoped) {
            $this->scoped[$alias] = true;
        }

        return $this;
    }

    /**
     * Binds a contextual attribute to a handler.
     *
     * The handler will be executed via {@see Container::call()} with an argument named
     * `attribute` containing the attribute instance.
     *
     * To receive the attribute instance, the handler should accept a parameter named
     * `$attribute` (type-hinted to the attribute class if desired).
     *
     * @param class-string<ContextualAttribute<mixed>> $attribute The attribute FQCN.
     * @param Closure $handler The handler.
     * @return static The Container instance.
     */
    public function bindAttribute(string $attribute, Closure $handler): static
    {
        $this->contextualAttributes[$attribute] = $handler;

        return $this;
    }

    /**
     * Builds a class instance, injecting dependencies as required.
     *
     * @template T of object
     *
     * @param class-string<T> $className The class name.
     * @param array<mixed> $arguments The constructor arguments.
     * @return T The class instance.
     *
     * @throws ContainerNotFoundException If the class is not valid.
     */
    public function build(string $className, array $arguments = []): mixed
    {
        if (!class_exists($className)) {
            throw new ContainerNotFoundException(sprintf(
                'Class `%s` does not exist.',
                $className
            ));
        }

        $reflection = new ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new ContainerNotFoundException(sprintf(
                'Class `%s` is not instantiable.',
                $className
            ));
        }

        $this->buildStack[] = $className;

        try {
            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                return new $className();
            }

            $parameters = $constructor->getParameters();

            $arguments = $this->resolveDependencies($parameters, $arguments);

            $this->addDependenciesToStack($arguments);

            return $reflection->newInstanceArgs($arguments);
        } finally {
            array_pop($this->buildStack);
        }
    }

    /**
     * Executes a callable using resolved dependencies.
     *
     * @param array{0: class-string|object, 1?: string}|object|string $callable The callable.
     * @param array<mixed> $arguments The function arguments.
     * @return mixed The return value of the callable.
     *
     * @throws ContainerException If a dependency cannot be resolved.
     */
    public function call(array|object|string $callable, array $arguments = []): mixed
    {
        if (is_string($callable) && str_contains($callable, '::')) {
            $callable = explode('::', $callable, 2);
        }

        if (is_array($callable)) {
            $target = $callable[0];
            $method = $callable[1] ?? '__invoke';

            if (!is_string($method)) {
                throw new ContainerException('Method name must be a string.');
            }

            if (!is_string($target) && !is_object($target)) {
                throw new ContainerException('Callable target must be a class-string or object.');
            }

            $reflection = new ReflectionMethod($target, $method);

            if ($reflection->isStatic()) {
                $target = null;
            } else if (is_string($target)) {
                $target = $this->use($target);
            }

            $arguments = $this->resolveDependencies($reflection->getParameters(), $arguments);

            $this->addDependenciesToStack($arguments);

            return $reflection->invokeArgs($target, $arguments);
        }

        if (is_string($callable) && class_exists($callable) && method_exists($callable, '__invoke')) {
            $callable = $this->use($callable);
        }

        assert(is_callable($callable));

        $reflection = new ReflectionFunction($callable(...));

        $arguments = $this->resolveDependencies($reflection->getParameters(), $arguments);

        $this->addDependenciesToStack($arguments);

        return $reflection->invokeArgs($arguments);
    }

    /**
     * Clears all scoped instances (but keep scoped bindings), including any dependents.
     *
     * @return static The Container instance.
     */
    public function clearScoped(): static
    {
        foreach ($this->scoped as $alias => $v) {
            $this->unset($alias, true);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $alias): mixed
    {
        return $this->use($alias);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function has(string $alias): bool
    {
        $seen = [];

        $current = $alias;

        while (true) {
            if (isset($seen[$current])) {
                return false;
            }

            $seen[$current] = true;

            if (!isset($this->bindings[$current])) {
                return class_exists($current) && (new ReflectionClass($current))->isInstantiable();
            }

            [$factory] = $this->bindings[$current];

            if (!is_string($factory)) {
                return true;
            }

            $current = $factory;
        }
    }

    /**
     * Binds an alias to a class instance.
     *
     * @template T
     *
     * @param string $alias The alias.
     * @param T $instance The class instance.
     * @return T The instance.
     */
    public function instance(string $alias, mixed $instance): mixed
    {
        $this->unset($alias);
        $this->unscoped($alias);

        unset($this->bindings[$alias]);

        $this->instances[$alias] = $instance;

        return $instance;
    }

    /**
     * Binds an alias to a factory Closure or class name as a reusable scoped instance.
     *
     * @param string $alias The alias.
     * @param Closure|string|null $factory The factory Closure or class name.
     * @return static The Container instance.
     */
    public function scoped(string $alias, Closure|string|null $factory = null): static
    {
        return $this->bind($alias, $factory, true, true);
    }

    /**
     * Binds an alias to a factory Closure or class name as a reusable instance.
     *
     * @param string $alias The alias.
     * @param Closure|string|null $factory The factory Closure or class name.
     * @return static The Container instance.
     */
    public function singleton(string $alias, Closure|string|null $factory = null): static
    {
        return $this->bind($alias, $factory, true);
    }

    /**
     * Removes an alias from the scoped instances.
     *
     * @param string $alias The alias.
     * @return static The Container instance.
     */
    public function unscoped(string $alias): static
    {
        unset($this->scoped[$alias]);

        return $this;
    }

    /**
     * Removes an instance and optionally any dependents.
     *
     * Dependent tracking is identity-based and only includes dependencies that are already
     * container-managed shared instances at resolution time (i.e. present in {@see $instances}).
     *
     * @param string $alias The alias.
     * @param bool $unsetDependents Whether to unset dependents.
     * @return static The Container instance.
     */
    public function unset(string $alias, bool $unsetDependents = false): static
    {
        $dependents = $unsetDependents ?
            array_keys($this->dependencyMap[$alias] ?? []) :
            [];

        unset($this->dependencyMap[$alias]);
        unset($this->instances[$alias]);

        foreach ($dependents as $dependent) {
            $this->unset((string) $dependent, true);
        }

        return $this;
    }

    /**
     * Resolves and returns an instance for the given alias.
     *
     * If the alias is bound as shared (singleton or scoped), the instance will only be cached
     * when invoked without manual arguments.
     *
     * @param string $alias The alias.
     * @param array<mixed> $arguments The constructor arguments.
     * @return mixed The class instance.
     *
     * @throws ContainerException If a dependency cannot be resolved.
     */
    public function use(string $alias, array $arguments = []): mixed
    {
        if (isset($this->instances[$alias]) && $arguments === []) {
            return $this->instances[$alias];
        }

        if (in_array($alias, $this->aliasStack, true)) {
            $cycle = [...$this->aliasStack, $alias];

            throw new ContainerException(sprintf(
                'Alias `%s` is dependent on itself. (%s)',
                $alias,
                implode(' > ', $cycle)
            ));
        }

        $this->aliasStack[] = $alias;
        $this->dependencyStack[] = [];

        try {
            [$factory, $shared] = $this->bindings[$alias] ?? [$alias, false];

            if (is_string($factory)) {
                if ($factory === $alias) {
                    assert(class_exists($factory));

                    /** @var class-string $className */
                    $className = $factory;

                    $instance = $this->build($className, $arguments);
                } else {
                    $instance = $this->use($factory, $arguments);
                }
            } else {
                $instance = $this->call($factory, $arguments);
            }
        } finally {
            $dependencies = array_pop($this->dependencyStack);
            array_pop($this->aliasStack);
        }

        if (!$shared || $arguments !== []) {
            return $instance;
        }

        foreach ($dependencies as $dependency) {
            $key = array_search($dependency, $this->instances, true);

            if ($key !== false) {
                $this->dependencyMap[$key] ??= [];
                $this->dependencyMap[$key][$alias] = true;
            }
        }

        return $this->instances[$alias] = $instance;
    }

    /**
     * Adds any dependencies to the stack.
     *
     * Only dependencies that are already container-managed shared instances will be recorded.
     *
     * @param mixed[] $arguments The resolved dependencies.
     */
    protected function addDependenciesToStack(array $arguments): void
    {
        $lastIndex = array_key_last($this->dependencyStack);

        if ($lastIndex === null) {
            return;
        }

        foreach ($arguments as $argument) {
            if (!is_object($argument) || !in_array($argument, $this->instances, true)) {
                continue;
            }

            $this->dependencyStack[$lastIndex][] = $argument;
        }
    }

    /**
     * Resolves dependencies from parameters.
     *
     * Named arguments (matching parameter names) are applied first. Any remaining arguments are
     * appended and passed positionally.
     *
     * @param ReflectionParameter[] $parameters The function parameters.
     * @param array<mixed> $arguments The provided arguments.
     * @return mixed[] The resolved dependencies (in invocation order).
     *
     * @throws ContainerException If a dependency cannot be resolved.
     */
    protected function resolveDependencies(array $parameters, array $arguments): array
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();

            if (array_key_exists($paramName, $arguments)) {
                $dependencies[] = $arguments[$paramName];
                unset($arguments[$paramName]);

                continue;
            }

            $attributes = $parameter->getAttributes(ContextualAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
            $attribute = $attributes[0] ?? null;

            if ($attribute) {
                $instance = $attribute->newInstance();
                $name = $attribute->getName();

                if (isset($this->contextualAttributes[$name])) {
                    $dependencies[] = $this->call($this->contextualAttributes[$name], ['attribute' => $instance]);
                } else {
                    $dependencies[] = $instance->resolve($this);
                }

                continue;
            }

            $paramType = $parameter->getType();
            $e = null;

            if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltIn()) {
                try {
                    $typeName = $paramType->getName();

                    $declaringClass = $parameter->getDeclaringClass();

                    $className = match ($typeName) {
                        'parent' => $declaringClass?->getParentClass() ?: null,
                        'self' => $declaringClass,
                        default => $typeName
                    };

                    if ($className instanceof ReflectionClass) {
                        $className = $className->getName();
                    }

                    if (!$className) {
                        throw new ContainerException(sprintf(
                            'Dependency `%s` could not be resolved.',
                            $paramName
                        ));
                    }

                    $index = array_search($className, $this->buildStack, true);

                    if ($index === false) {
                        $dependencies[] = $this->use($className);

                        continue;
                    }

                    $dependents = array_map(
                        fn(string $dependent): string => '`'.$dependent.'`',
                        array_slice($this->buildStack, (int) $index)
                    );

                    throw new ContainerException(sprintf(
                        'Class `%s` is dependent on itself. (%s)',
                        $className,
                        implode(' > ', $dependents)
                    ));
                } catch (ContainerException $e) {
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else if ($parameter->allowsNull()) {
                $dependencies[] = null;
            } else if (!$parameter->isVariadic()) {
                throw $e ?? new ContainerException(sprintf(
                    'Dependency `%s` could not be resolved.',
                    $paramName
                ));
            }
        }

        $arguments = array_values($arguments);

        return array_merge($dependencies, $arguments);
    }
}
