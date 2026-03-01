<?php
declare(strict_types=1);

namespace Fyre\Router\Middleware;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\ORM\Entity;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionNamedType;

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
     * Parameters typed as {@see Entity} are resolved via model route bindings and replace
     * the original scalar argument value.
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

        $parent = null;

        foreach ($params as $param) {
            $name = $param->getName();

            if (!array_key_exists($name, $arguments)) {
                continue;
            }

            $type = $param->getType();

            if (!($type instanceof ReflectionNamedType)) {
                continue;
            }

            $typeName = $type->getName();

            if (!is_subclass_of($typeName, Entity::class)) {
                continue;
            }

            if ($arguments[$name] === null && $type->allowsNull()) {
                $parent = null;

                continue;
            }

            if ($arguments[$name] !== null) {
                $Model = $this->entityLocator->findAlias($typeName) |> $this->modelRegistry->use(...);
                $field = $fields[$name] ?? $Model->getRouteKey();

                $entity = $Model->resolveRouteBinding($arguments[$name], $field, $parent);
            } else {
                $entity = null;
            }

            if (!$entity) {
                throw new NotFoundException();
            }

            $parent = $entity;
            $arguments[$name] = $entity;
        }

        return $request->withAttribute('routeArguments', $arguments) |> $handler->handle(...);
    }
}
