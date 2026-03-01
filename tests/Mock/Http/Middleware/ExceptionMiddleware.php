<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Middleware;

use Exception;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ExceptionMiddleware
 */
class ExceptionMiddleware implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new Exception('Error');
    }
}
