<?php
declare(strict_types=1);

namespace Fyre\Security\Middleware;

use BadMethodCallException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\CsrfProtection;
use Fyre\Security\Exceptions\CsrfTokenException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP middleware that enforces CSRF protection.
 */
class CsrfProtectionMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs a CsrfProtectionMiddleware.
     *
     * @param CsrfProtection $csrfProtection The CsrfProtection.
     */
    public function __construct(
        protected CsrfProtection $csrfProtection
    ) {}

    /**
     * {@inheritDoc}
     *
     * @throws BadMethodCallException If CSRF protection has already been enabled.
     * @throws CsrfTokenException If the token is invalid.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->csrfProtection->checkToken($request);

        return $this->csrfProtection->beforeResponse($request, $handler->handle($request));
    }
}
