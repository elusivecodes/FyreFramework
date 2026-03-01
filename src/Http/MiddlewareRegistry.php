<?php
declare(strict_types=1);

namespace Fyre\Http;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function explode;
use function is_string;
use function is_subclass_of;
use function sprintf;
use function str_contains;

/**
 * Registers middleware aliases and groups and resolves them into executable middleware
 * instances/callables for use by {@see RequestHandler}.
 */
class MiddlewareRegistry
{
    use DebugTrait;

    /**
     * @var array<string, class-string<MiddlewareInterface>|Closure>
     */
    protected array $aliases = [];

    /**
     * @var array<string, Closure|MiddlewareInterface>
     */
    protected array $instances = [];

    /**
     * Constructs a MiddlewareRegistry.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Clears all aliases and middleware.
     */
    public function clear(): void
    {
        $this->aliases = [];
        $this->instances = [];
    }

    /**
     * Maps an alias to a middleware group.
     *
     * @param string $alias The middleware alias.
     * @param array<Closure|MiddlewareInterface|string> $middleware The Middleware group.
     * @return static The MiddlewareRegistry instance.
     */
    public function group(string $alias, array $middleware): static
    {
        $this->aliases[$alias] = fn(): Closure => fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $this->container->build(RequestHandler::class, [
            'queue' => new MiddlewareQueue($middleware),
            'fallbackHandler' => $handler,
        ])->handle($request);

        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Maps an alias to middleware.
     *
     * @param string $alias The middleware alias.
     * @param class-string<MiddlewareInterface>|Closure $middleware The middleware class, or a function that returns middleware.
     * @param array<string, mixed> $arguments The additional arguments for the middleware.
     * @return static The MiddlewareRegistry instance.
     */
    public function map(string $alias, Closure|string $middleware, array $arguments = []): static
    {
        if (!is_string($middleware)) {
            $this->aliases[$alias] = fn(): Closure|MiddlewareInterface => $this->container->call($middleware, $arguments);
        } else if ($arguments !== []) {
            $this->aliases[$alias] = fn(): MiddlewareInterface => $this->container->build($middleware, $arguments);
        } else {
            $this->aliases[$alias] = $middleware;
        }

        unset($this->instances[$alias]);

        return $this;
    }

    /**
     * Returns resolved middleware.
     *
     * String middleware may include inline arguments using the `alias:arg1,arg2` format. When
     * resolving a PSR-15 {@see MiddlewareInterface}, it is converted to its `process()`
     * callable and the inline arguments are appended after `$request` and `$handler`.
     *
     * @param Closure|MiddlewareInterface|string $middleware The Middleware.
     * @return Closure|MiddlewareInterface The Middleware.
     */
    public function resolve(Closure|MiddlewareInterface|string $middleware): Closure|MiddlewareInterface
    {
        if (is_string($middleware)) {
            if (!str_contains($middleware, ':')) {
                return $this->use($middleware);
            }

            [$alias, $args] = explode(':', $middleware, 2);
            $middleware = $this->use($alias);
            $args = explode(',', $args);

            if ($middleware instanceof MiddlewareInterface) {
                $middleware = $middleware->process(...);
            }

            return static fn(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface => $middleware($request, $handler, ...$args);
        }

        return $middleware;
    }

    /**
     * Returns a shared Middleware instance.
     *
     * Instances are cached per alias; calling {@see MiddlewareRegistry::map()} or
     * {@see MiddlewareRegistry::group()} clears the cached instance for that alias.
     *
     * @param string $alias The middleware alias.
     * @return Closure|MiddlewareInterface The Middleware.
     */
    public function use(string $alias): Closure|MiddlewareInterface
    {
        return $this->instances[$alias] ??= $this->build($alias);
    }

    /**
     * Builds a Middleware.
     *
     * @param string $alias The middleware alias.
     * @return Closure|MiddlewareInterface The Middleware.
     *
     * @throws InvalidArgumentException If the middleware is not valid.
     */
    protected function build(string $alias): Closure|MiddlewareInterface
    {
        $middleware = $this->aliases[$alias] ?? $alias;

        if (is_string($middleware) && is_subclass_of($middleware, MiddlewareInterface::class)) {
            return $this->container->build($middleware);
        }

        if ($middleware instanceof Closure) {
            return $middleware();
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid middleware: %s',
            $middleware
        ));
    }
}
