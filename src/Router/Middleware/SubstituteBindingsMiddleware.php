<?php
declare(strict_types=1);

namespace Fyre\Router\Middleware;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\ORM\Entity;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Utility\EnumHelper;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionNamedType;
use UnitEnum;

use function array_key_exists;
use function is_subclass_of;

/**
 * HTTP middleware that substitutes route parameters with bound values.
 */
class SubstituteBindingsMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs a SubstituteBindingsMiddleware.
     *
     * @param Container $container The Container.
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     * @param EntityLocator $entityLocator The EntityLocator.
     */
    public function __construct(
        protected Container $container,
        protected ModelRegistry $modelRegistry,
        protected EntityLocator $entityLocator
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: Route arguments are substituted based on the route destination signature.
     * Custom binding callbacks take precedence over the default binding behavior.
     * Parameters typed as {@see Entity} are resolved via model route bindings and replace
     * the original scalar argument value. Parameters typed as enums are parsed from the
     * route argument value.
     *
     * @throws NotFoundException If a route parameter cannot be resolved.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route) {
            return $handler->handle($request);
        }

        $arguments = $request->getAttribute('routeArguments', []);

        if ($arguments === []) {
            return $handler->handle($request);
        }

        $params = $route->getParameters();
        $fields = $route->getBindingFields();
        $bindingCallbacks = $route->getBindingCallbacks();

        $parent = null;

        foreach ($params as $param) {
            $name = $param->getName();

            if (!array_key_exists($name, $arguments)) {
                continue;
            }

            $value = $arguments[$name];
            $callback = $bindingCallbacks[$name] ?? null;
            $type = $param->getType();
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;

            if ($value === null) {
                if ($param->isDefaultValueAvailable()) {
                    unset($arguments[$name]);
                    $request = $request->withAttribute('routeArguments', $arguments);
                } else if (!$param->allowsNull()) {
                    throw new NotFoundException();
                }

                if ($typeName !== null && is_subclass_of($typeName, Entity::class)) {
                    $parent = null;
                }

                continue;
            }

            if ($callback) {
                $value = $this->container->call(
                    $callback,
                    [
                        'value' => $value,
                        'request' => $request,
                    ]
                );
            } else if ($typeName !== null) {
                if (is_subclass_of($typeName, Entity::class)) {
                    $Model = $this->entityLocator->findAlias($typeName) |> $this->modelRegistry->use(...);
                    $field = $fields[$name] ?? $Model->getRouteKey();
                    $value = $Model->resolveRouteBinding($value, $field, $parent);
                } else if (is_subclass_of($typeName, UnitEnum::class)) {
                    $value = EnumHelper::parseValue($typeName, $value);
                } else {
                    continue;
                }
            } else {
                continue;
            }

            if ($value === null) {
                throw new NotFoundException();
            }

            if ($value instanceof Entity) {
                $parent = $value;
            }

            $arguments[$name] = $value;
            $request = $request->withAttribute('routeArguments', $arguments);
        }

        return $handler->handle($request);
    }
}
