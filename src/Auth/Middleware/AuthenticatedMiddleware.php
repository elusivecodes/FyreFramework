<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\UnauthorizedException;
use Fyre\Http\Negotiate;
use Fyre\Http\RedirectResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP middleware that requires an authenticated user.
 */
class AuthenticatedMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs an AuthenticatedMiddleware.
     *
     * @param Auth $auth The Auth.
     */
    public function __construct(
        protected Auth $auth
    ) {}

    /**
     * {@inheritDoc}
     *
     * For HTML requests, this middleware redirects unauthenticated users to the login route and includes the
     * current URL as a redirect parameter. For API requests, it throws {@see UnauthorizedException}.
     *
     * @throws UnauthorizedException If the user is not authenticated and the request expects JSON.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->auth->isLoggedIn()) {
            return $handler->handle($request);
        }

        if (Negotiate::content($request->getHeaderLine('Accept'), ['text/html', 'application/json']) === 'application/json') {
            throw new UnauthorizedException();
        }

        $redirect = $request->getUri() |> $this->auth->getLoginUrl(...);

        return new RedirectResponse($redirect);
    }
}
