<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Middleware;

use Fyre\Http\ClientResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_map;
use function assert;

class RouteArgsMiddleware implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, string ...$args): ResponseInterface
    {
        $routeArguments = $request->getAttribute('routeArguments') ?? [];
        $args = array_map(
            static fn(string $arg): mixed => $routeArguments[$arg] ?? null,
            $args
        );

        $response = $handler->handle($request);

        assert($response instanceof ClientResponse);

        return $response->withJson($args);
    }
}
