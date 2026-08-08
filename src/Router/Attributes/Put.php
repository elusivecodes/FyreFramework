<?php
declare(strict_types=1);

namespace Fyre\Router\Attributes;

use Attribute;
use Closure;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Route attribute for PUT requests.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Put extends Route
{
    /**
     * Constructs a Put.
     *
     * @param string|null $path The route path.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     */
    public function __construct(
        string|null $path = null,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = [],
    ) {
        parent::__construct(
            $path,
            $scheme,
            $host,
            $port,
            ['PUT'],
            $middleware,
            $placeholders,
            $as,
            $bindingCallbacks
        );
    }
}
