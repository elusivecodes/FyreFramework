<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MockMiddleware implements MiddlewareInterface
{
    protected bool $loaded = false;

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->loaded = true;

        return $handler->handle($request);
    }
}
