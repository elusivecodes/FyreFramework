<?php
declare(strict_types=1);

namespace Fyre\Security\Middleware;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\ContentSecurityPolicy;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP middleware that applies CSP headers.
 */
class CspMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs a CspMiddleware.
     *
     * @param ContentSecurityPolicy $csp The ContentSecurityPolicy.
     */
    public function __construct(
        protected ContentSecurityPolicy $csp
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: CSP headers are applied to the response returned by the next handler.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request) |> $this->csp->addHeaders(...);
    }
}
