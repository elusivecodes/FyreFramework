<?php
declare(strict_types=1);

namespace Fyre\Router\Attributes;

use Attribute;
use Closure;
use Fyre\Router\RouteLocator;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Attribute that defines routing metadata for a controller or action.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    /**
     * Constructs a Route.
     *
     * @param string|null $path The route path.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[]|null $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     */
    public function __construct(
        protected string|null $path = null,
        protected string|null $scheme = null,
        protected string|null $host = null,
        protected int|null $port = null,
        protected array|null $methods = null,
        protected array $middleware = [],
        protected array $placeholders = [],
        protected string|null $as = null,
    ) {}

    /**
     * Returns the route data.
     *
     * Note: The {@see RouteLocator} merges method-level route data with any
     * class-level defaults when building routes.
     *
     * @return array<string, mixed> The route data.
     */
    public function getRoute(): array
    {
        return [
            'path' => $this->path,
            'scheme' => $this->scheme,
            'host' => $this->host,
            'port' => $this->port,
            'methods' => $this->methods,
            'middleware' => $this->middleware,
            'placeholders' => $this->placeholders,
            'as' => $this->as,
        ];
    }
}
