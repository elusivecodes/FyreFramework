<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_map;

class RouteArgsMiddleware implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, string ...$args): ResponseInterface
    {
        $routeArguments = $request->getAttribute('routeArguments') ?? [];
        $args = array_map(
            fn(string $arg): mixed => $routeArguments[$arg] ?? null,
            $args
        );

        return $handler->handle($request)->withJson($args);
    }
}
