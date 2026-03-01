<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ArgsMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected int|null $a = null,
        protected int|null $b = null
    ) {}

    public function getArgs(): array
    {
        return [$this->a, $this->b];
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, string ...$args): ResponseInterface
    {
        return $handler->handle($request)->withJson($args);
    }
}
