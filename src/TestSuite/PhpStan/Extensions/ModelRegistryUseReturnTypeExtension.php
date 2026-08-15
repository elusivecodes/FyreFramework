<?php
declare(strict_types=1);

namespace Fyre\TestSuite\PhpStan\Extensions;

use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

use function array_values;
use function in_array;
use function trim;

/**
 * Resolves ModelRegistry::use() calls to concrete mock model classes.
 */
class ModelRegistryUseReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * @var string[]
     */
    protected array $modelNamespaces;

    /**
     * @var array<array{classes: string[], modelNamespaces: string[]}>
     */
    protected array $modelNamespacesOverrides;

    /**
     * @param ReflectionProvider $reflectionProvider The reflection provider.
     * @param string[] $modelNamespaces The model namespaces.
     * @param array<array{classes: string[], modelNamespaces: string[]}> $modelNamespacesOverrides The model namespace overrides.
     */
    public function __construct(
        protected ReflectionProvider $reflectionProvider,
        array $modelNamespaces = ['App\\Models'],
        array $modelNamespacesOverrides = []
    ) {
        $this->modelNamespaces = $modelNamespaces;
        $this->modelNamespacesOverrides = $modelNamespacesOverrides;
    }

    /**
     * Gets the class supported by this extension.
     *
     * @return class-string<ModelRegistry> The supported class.
     */
    #[Override]
    public function getClass(): string
    {
        return ModelRegistry::class;
    }

    /**
     * Gets the return type for a method call.
     *
     * @param MethodReflection $methodReflection The method reflection.
     * @param MethodCall $methodCall The method call.
     * @param Scope $scope The scope.
     * @return Type The return type.
     */
    #[Override]
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $args = $methodCall->getArgs();

        if ($args === []) {
            return new ObjectType(Model::class);
        }

        $aliasType = $scope->getType($args[0]->value);
        $classAliases = isset($args[1]) ?
            $scope->getType($args[1]->value)->getConstantStrings() :
            $aliasType->getConstantStrings();

        foreach ($classAliases as $classAlias) {
            if ($modelClass = $this->resolveModelClass($classAlias->getValue(), $scope)) {
                return new ObjectType($modelClass);
            }
        }

        return new ObjectType(Model::class);
    }

    /**
     * Checks whether the method is supported.
     *
     * @param MethodReflection $methodReflection The method reflection.
     * @return bool Whether the method is supported.
     */
    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'use';
    }

    /**
     * Gets the configured model namespaces for a scope.
     *
     * @param Scope $scope The scope.
     * @return string[] The model namespaces.
     */
    protected function modelNamespaces(Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection !== null) {
            $className = $classReflection->getName();

            foreach ($this->modelNamespacesOverrides as $override) {
                if (in_array($className, $override['classes'], true)) {
                    return array_values($override['modelNamespaces']);
                }
            }
        }

        return array_values($this->modelNamespaces);
    }

    /**
     * Resolves the model class for a class alias.
     *
     * @param string $classAlias The model class alias.
     * @param Scope $scope The scope.
     * @return class-string<Model>|null The model class.
     */
    protected function resolveModelClass(string $classAlias, Scope $scope): string|null
    {
        foreach ($this->modelNamespaces($scope) as $namespace) {
            $className = trim($namespace, '\\').'\\'.trim($classAlias, '\\').'Model';

            if (!$this->reflectionProvider->hasClass($className)) {
                continue;
            }

            $classReflection = $this->reflectionProvider->getClass($className);

            if (!$classReflection->isSubclassOf(Model::class)) {
                continue;
            }

            /** @var class-string<Model> $modelClass */
            $modelClass = $classReflection->getName();

            return $modelClass;
        }

        return null;
    }
}
