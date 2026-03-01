<?php
declare(strict_types=1);

namespace Fyre\Router\Middleware;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Router\Router;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Routes requests through the Router.
 */
class RouterMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs a RouterMiddleware.
     *
     * @param Container $container The Container.
     * @param MiddlewareRegistry $middlewareRegistry The MiddlewareRegistry.
     * @param Router $router The Router.
     */
    public function __construct(
        protected Container $container,
        protected MiddlewareRegistry $middlewareRegistry,
        protected Router $router
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: The Router sets `relativePath`, `route`, and `routeArguments` request attributes.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->router->parseRequest($request) |> $handler->handle(...);
    }
}
