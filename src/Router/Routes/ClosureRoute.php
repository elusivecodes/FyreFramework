<?php
declare(strict_types=1);

namespace Fyre\Router\Routes;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Router\Route;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionFunction;

use function assert;

/**
 * Dispatches to a Closure.
 */
class ClosureRoute extends Route
{
    use MacroTrait;

    /**
     * Constructs a ClosureRoute.
     *
     * @param Container $container The Container.
     * @param Closure $destination The destination.
     * @param string $path The path.
     * @param string|null $scheme The scheme.
     * @param string|null $host The host.
     * @param int|null $port The port.
     * @param string[]|null $methods The methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The middleware.
     * @param array<string, string> $placeholders The placeholders.
     */
    public function __construct(
        Container $container,
        Closure $destination,
        string $path = '',
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array|null $methods = null,
        array $middleware = [],
        array $placeholders = []
    ) {
        parent::__construct(
            $container,
            $destination,
            $path,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getParameters(): array
    {
        assert($this->destination instanceof Closure);

        return new ReflectionFunction($this->destination)->getParameters();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function process(ServerRequestInterface $request): ResponseInterface|string
    {
        assert($this->destination instanceof Closure);

        return $this->container->call($this->destination, [
            'request' => $request,
            ...$request->getAttribute('routeArguments', []),
        ]);
    }
}
