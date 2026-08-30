<?php
declare(strict_types=1);

namespace Fyre\Router;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Uri;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionParameter;

use function count;
use function explode;
use function getservbyname;
use function is_string;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_split;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function substr;

use const PREG_SET_ORDER;
use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_UNMATCHED_AS_NULL;

/**
 * Provides a base route definition.
 *
 * Note: Placeholders in route paths use `{name}` syntax, may appear within a segment,
 * and can specify a binding field via `{name:field}`. Optional placeholders use
 * `{name?}` syntax and must occupy an entire segment.
 */
abstract class Route
{
    use DebugTrait;

    public const PLACEHOLDER_REGEXP = '`(/?)\{([^}]+)\}`';

    /**
     * @var array<string, string|null>|null
     */
    protected array|null $bindingFields = null;

    /**
     * Parses a route placeholder.
     *
     * @param string $placeholder The placeholder definition.
     * @return array{string, string|null, bool} The name, binding field and optional flag.
     */
    public static function parsePlaceholder(string $placeholder): array
    {
        $optional = str_ends_with($placeholder, '?');

        if ($optional) {
            $placeholder = substr($placeholder, 0, -1);
        }

        if (str_contains($placeholder, ':')) {
            [$name, $field] = explode(':', $placeholder, 2);
        } else {
            $name = $placeholder;
            $field = null;
        }

        return [$name, $field, $optional];
    }

    /**
     * Constructs a Route.
     *
     * @param Container $container The Container.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The destination.
     * @param string|null $scheme The scheme.
     * @param string|null $host The host.
     * @param int|null $port The port.
     * @param string[]|null $methods The methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The middleware.
     * @param array<string, string> $placeholders The placeholders.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     *
     * @throws InvalidArgumentException If the path or port is not valid.
     */
    public function __construct(
        protected Container $container,
        protected array|Closure|string $destination,
        protected string $path = '',
        protected string|null $scheme = null,
        protected string|null $host = null,
        protected int|null $port = null,
        protected array|null $methods = null,
        protected array $middleware = [],
        protected array $placeholders = [],
        protected array $bindingCallbacks = []
    ) {
        if (preg_match('`[^/]\{[^}]+\?\}|\{[^}]+\?\}[^/]`', $this->path)) {
            throw new InvalidArgumentException('Optional route placeholders must occupy an entire path segment.');
        }

        if ($this->host !== null) {
            $this->setHost($this->host);
        }

        if ($this->port !== null) {
            $this->setPort($this->port);
        }
    }

    /**
     * Returns the route binding callbacks.
     *
     * @return array<string, Closure> The route binding callbacks.
     */
    public function getBindingCallbacks(): array
    {
        return $this->bindingCallbacks;
    }

    /**
     * Returns the route binding fields.
     *
     * Note: This is derived from placeholders in the route path and cached after first
     * access.
     *
     * @return array<string, string|null> The route binding fields.
     */
    public function getBindingFields(): array
    {
        if ($this->bindingFields !== null) {
            return $this->bindingFields;
        }

        preg_match_all(static::PLACEHOLDER_REGEXP, $this->path, $placeholders, PREG_SET_ORDER);

        $this->bindingFields = [];

        foreach ($placeholders as $placeholder) {
            [$name, $field] = static::parsePlaceholder($placeholder[2]);

            $this->bindingFields[$name] = $field;
        }

        return $this->bindingFields;
    }

    /**
     * Returns the route destination.
     *
     * @return array{0: class-string, 1?: string}|Closure|string The route destination.
     */
    public function getDestination(): array|Closure|string
    {
        return $this->destination;
    }

    /**
     * Returns the route host.
     *
     * @return string|null The route host.
     */
    public function getHost(): string|null
    {
        return $this->host;
    }

    /**
     * Returns the route methods.
     *
     * @return string[]|null The route methods, or null if the default methods are used.
     */
    public function getMethods(): array|null
    {
        return $this->methods;
    }

    /**
     * Returns the route middleware.
     *
     * @return array<Closure|MiddlewareInterface|string> The route middleware.
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Returns the reflection parameters.
     *
     * @return ReflectionParameter[] The reflection parameters.
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * Returns the route path.
     *
     * @return string The route path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the route placeholders.
     *
     * @return array<string, string> The route placeholders.
     */
    public function getPlaceholders(): array
    {
        return $this->placeholders;
    }

    /**
     * Returns the route port.
     *
     * @return int|null The route port.
     */
    public function getPort(): int|null
    {
        return $this->port;
    }

    /**
     * Returns the route scheme.
     *
     * @return string|null The route scheme.
     */
    public function getScheme(): string|null
    {
        return $this->scheme;
    }

    /**
     * Handles the route.
     *
     * Note: If the route returns a string, it will be wrapped in a {@see ClientResponse}
     * body.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ResponseInterface The Response instance.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->process($request);

        if (!is_string($result)) {
            return $result;
        }

        return $this->container->use(ClientResponse::class, [
            'options' => [
                'body' => $result,
            ],
        ]);
    }

    /**
     * Matches a ServerRequest without checking its HTTP method.
     *
     * Note: When matched, this sets the `route` and `routeArguments` request attributes.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ServerRequestInterface|null The ServerRequest or null if not matched.
     */
    public function matchRequest(ServerRequestInterface $request): ServerRequestInterface|null
    {
        $uri = $request->getUri();

        if ($this->scheme && $uri->getScheme() !== $this->scheme) {
            return null;
        }

        if ($this->host && !preg_match($this->getHostRegExp(), $uri->getHost())) {
            return null;
        }

        if ($this->port && $uri->getPort() !== $this->port && $this->port !== getservbyname($uri->getScheme(), 'tcp')) {
            return null;
        }

        $path = $request->getAttribute('relativePath');

        if (!$path) {
            $path = $uri->getPath() |> Router::normalizePath(...);
        }

        [$pathRegExp, $captures] = $this->getPathRegExp();

        if (!preg_match($pathRegExp, $path, $matches, PREG_UNMATCHED_AS_NULL)) {
            return null;
        }

        $arguments = [];

        foreach ($captures as $name => $capture) {
            $arguments[$name] = $matches[$capture] ?? null;
        }

        return $request
            ->withAttribute('route', $this)
            ->withAttribute('routeArguments', $arguments);
    }

    /**
     * Sets a route binding callback.
     *
     * @param string $parameter The route parameter.
     * @param Closure $callback The route binding callback.
     * @return static The Route.
     */
    public function setBindingCallback(string $parameter, Closure $callback): static
    {
        $this->bindingCallbacks[$parameter] = $callback;

        return $this;
    }

    /**
     * Sets the route host.
     *
     * @param string $host The route host.
     * @return static The Route.
     */
    public function setHost(string $host): static
    {
        $this->host = new Uri()
            ->withHost($host)
            ->getHost();

        return $this;
    }

    /**
     * Sets the route methods.
     *
     * @param string[] $methods The route methods.
     * @return static The Route.
     */
    public function setMethods(array $methods): static
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Sets the route middleware.
     *
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @return static The Route.
     */
    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;

        return $this;
    }

    /**
     * Sets a route placeholder.
     *
     * @param string $placeholder The route placeholder.
     * @param string $regex The route placeholder regex.
     * @return static The Route.
     */
    public function setPlaceholder(string $placeholder, string $regex): static
    {
        $this->placeholders[$placeholder] = $regex;

        return $this;
    }

    /**
     * Sets the route port.
     *
     * @param int $port The route port.
     * @return static The Route.
     *
     * @throws InvalidArgumentException If the port is not valid.
     */
    public function setPort(int $port): static
    {
        if ($port <= 0 || $port > 65535) {
            throw new InvalidArgumentException('Route port must be between 1 and 65535.');
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Sets the route scheme.
     *
     * @param string $scheme The route scheme.
     * @return static The Route.
     */
    public function setScheme(string $scheme): static
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Returns the route host regular expression.
     *
     * Note: Hosts support `*` wildcards (e.g. `*.example.com`).
     *
     * @return string The route host regular expression.
     */
    protected function getHostRegExp(): string
    {
        return '`^'.str_replace('\*', '.*', preg_quote($this->host ?? '', '`').'\z`');
    }

    /**
     * Returns the route path regular expression.
     *
     * Note: Static path segments are escaped and placeholders use generated named captures
     * so custom placeholder captures do not affect argument extraction.
     *
     * @return array{string, array<string, string>} The route path regular expression and captures.
     */
    protected function getPathRegExp(): array
    {
        $parts = preg_split(
            static::PLACEHOLDER_REGEXP,
            Router::normalizePath($this->path),
            flags: PREG_SPLIT_DELIM_CAPTURE
        ) ?: [];

        $path = preg_quote($parts[0] ?? '', '`');
        $captures = [];

        for ($i = 1; $i < count($parts); $i += 3) {
            [$name, , $optional] = static::parsePlaceholder($parts[$i + 1]);

            $capture = 'routeArgument'.$i;
            $pattern = $this->placeholders[$name] ?? '[^/]+';

            $path .= $optional ?
                '(?:'.$parts[$i].'(?<'.$capture.'>'.$pattern.'))?' :
                $parts[$i].'(?<'.$capture.'>'.$pattern.')';

            $path .= preg_quote($parts[$i + 2], '`');

            $captures[$name] = $capture;
        }

        return ['`^'.$path.'\z`u', $captures];
    }

    /**
     * Processes the route.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ResponseInterface|string The Response or string response.
     */
    abstract protected function process(ServerRequestInterface $request): ResponseInterface|string;
}
