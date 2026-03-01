<?php
declare(strict_types=1);

namespace Fyre\Http\Middleware;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Session\Session;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function in_array;

/**
 * Starts a session for the request and injects it into the request attributes under the
 * `session` key.
 */
class SessionMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs a SessionMiddleware.
     *
     * @param Session $session The Session.
     */
    public function __construct(
        protected Session $session
    ) {}

    /**
     * {@inheritDoc}
     *
     * For safe HTTP methods (`GET`, `HEAD`, `OPTIONS`, `TRACE`) this will start a read-only
     * session when {@see Session::allowReadOnly()} returns true; otherwise it starts a normal
     * session.
     *
     * The session is exposed to downstream middleware/handlers via
     * `$request->getAttribute('session')`.
     *
     * Note: The session is closed after the handler returns a response. If the handler throws
     * an exception, the session close is attempted.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE'], true) && $this->session->allowReadOnly()) {
            $this->session->startReadOnly();
        } else {
            $this->session->start();
        }

        $request = $request->withAttribute('session', $this->session);

        try {
            return $handler->handle($request);
        } finally {
            $this->session->close();
        }
    }
}
