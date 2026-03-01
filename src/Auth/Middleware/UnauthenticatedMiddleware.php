<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\NotFoundException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * HTTP middleware that requires the user to be unauthenticated.
 */
class UnauthenticatedMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs an UnauthenticatedMiddleware.
     *
     * @param Auth $auth The Auth.
     */
    public function __construct(
        protected Auth $auth
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: When the user is already authenticated, this middleware throws {@see NotFoundException} to avoid
     * revealing the existence of routes intended only for unauthenticated users.
     *
     * @throws NotFoundException If the user is authenticated.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->auth->isLoggedIn()) {
            return $handler->handle($request);
        }

        throw new NotFoundException();
    }
}
