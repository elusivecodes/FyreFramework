<?php
declare(strict_types=1);

namespace Fyre\Auth\Middleware;

use Fyre\Auth\Auth;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\Http\Negotiate;
use Fyre\Http\RedirectResponse;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_key_exists;
use function array_map;

/**
 * HTTP middleware that enforces authorization rules.
 */
class AuthorizedMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs an AuthorizedMiddleware.
     *
     * @param Auth $auth The Auth.
     */
    public function __construct(
        protected Auth $auth
    ) {}

    /**
     * {@inheritDoc}
     *
     * Note: The first argument must be the access rule name. String arguments that match keys in the route
     * arguments are replaced with the corresponding route values.
     *
     * @throws ForbiddenException If the user is authenticated or the request expects JSON and access is denied.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler, mixed ...$args): ResponseInterface
    {
        $routeArguments = $request->getAttribute('routeArguments') ?? [];
        $args = array_map(
            fn(string $arg): mixed => array_key_exists($arg, $routeArguments) ?
                $routeArguments[$arg] :
                $arg,
            $args
        );

        if ($this->auth->access()->allows(...$args)) {
            return $handler->handle($request);
        }

        if (
            $this->auth->isLoggedIn() ||
            Negotiate::content($request->getHeaderLine('Accept'), ['text/html', 'application/json']) === 'application/json'
        ) {
            throw new ForbiddenException();
        }

        $redirect = $request->getUri() |> $this->auth->getLoginUrl(...);

        return new RedirectResponse($redirect);
    }
}
