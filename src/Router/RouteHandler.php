<?php
declare(strict_types=1);

namespace Fyre\Router;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\RequestHandler;
use Fyre\Router\Exceptions\RouterException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * PSR-15 request handler that dispatches a request to a matched route.
 */
class RouteHandler implements RequestHandlerInterface
{
    use DebugTrait;

    /**
     * Constructs a RouteHandler.
     *
     * @param Container $container The Container.
     * @param MiddlewareRegistry $middlewareRegistry The MiddlewareRegistry.
     */
    public function __construct(
        protected Container $container,
        protected MiddlewareRegistry $middlewareRegistry
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: This expects the request to have a `route` attribute (set by router middleware).
     * Route middleware is executed before invoking {@see Route::handle()}.
     *
     * @throws RouterException If the router middleware has not been loaded.
     */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route) {
            throw new RouterException('Route middleware has not been loaded.');
        }

        $routeMiddleware = $route->getMiddleware();

        if ($routeMiddleware === []) {
            return $route->handle($request);
        }

        $routeMiddleware[] = static fn(ServerRequestInterface $request): ResponseInterface => $request->getAttribute('route')->handle($request);

        $routeQueue = new MiddlewareQueue($routeMiddleware);

        $routeHandler = $this->container->build(RequestHandler::class, [
            'queue' => $routeQueue,
        ]);

        return $routeHandler->handle($request);
    }
}
