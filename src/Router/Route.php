<?php
declare(strict_types=1);

namespace Fyre\Router;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionParameter;

use function array_keys;
use function array_shift;
use function explode;
use function getservbyname;
use function in_array;
use function is_string;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function preg_replace_callback;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function substr;

use const PREG_SET_ORDER;

/**
 * Provides a base route definition.
 *
 * Note: Placeholders in route paths use `{name}` syntax, may be marked optional with `?`
 * (e.g. `{id?}`), and can specify a binding field via `{name:field}`.
 */
abstract class Route
{
    use DebugTrait;

    /**
     * @var array<string, string|null>|null
     */
    protected array|null $bindingFields = null;

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
        protected array $placeholders = []
    ) {}

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

        preg_match_all('/\{([^\}]+)\}/', $this->path, $placeholders, PREG_SET_ORDER);

        $this->bindingFields = [];

        foreach ($placeholders as $placeholder) {
            $name = $placeholder[1];
            $field = null;

            if (str_ends_with($name, '?')) {
                $name = substr($name, 0, -1);
            }

            if (str_contains($name, ':')) {
                [$name, $field] = explode(':', $name, 2);
            }

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

        return $this->container->build(ClientResponse::class, [
            'options' => [
                'body' => $result,
            ],
        ]);
    }

    /**
     * Parses a ServerRequest.
     *
     * Note: When matched, this sets the `route` and `routeArguments` request attributes.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ServerRequestInterface|null The ServerRequest or null if not matched.
     */
    public function parseRequest(ServerRequestInterface $request): ServerRequestInterface|null
    {
        if ($this->methods !== null && !in_array($request->getMethod(), $this->methods, true)) {
            return null;
        }

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

        if (!preg_match($this->getPathRegExp(), $path, $matches)) {
            return null;
        }

        array_shift($matches);

        $arguments = [];
        $parameters = $this->getBindingFields() |> array_keys(...);

        foreach ($parameters as $i => $name) {
            $arguments[$name] = $matches[$i] ?? null;
        }

        return $request
            ->withAttribute('route', $this)
            ->withAttribute('routeArguments', $arguments);
    }

    /**
     * Sets the route host.
     *
     * @param string $host The route host.
     * @return static The Route.
     */
    public function setHost(string $host): static
    {
        $this->host = $host;

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
     */
    public function setPort(int $port): static
    {
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
        return '`^'.str_replace('\*', '.*', preg_quote($this->host ?? '', '`').'$`');
    }

    /**
     * Returns the route path regular expression.
     *
     * Note: Placeholders are expanded into capture groups; optional placeholders are
     * wrapped so the full segment is optional.
     *
     * @return string The route path regular expression.
     */
    protected function getPathRegExp(): string
    {
        $path = (string) preg_replace_callback(
            '/\/\{([^\}]+)\}/',
            function(array $match): string {
                $placeholder = $match[1];

                $optional = false;
                if (str_ends_with($placeholder, '?')) {
                    $placeholder = substr($placeholder, 0, -1);
                    $optional = true;
                }

                if (isset($this->placeholders[$placeholder])) {
                    $pattern = $this->placeholders[$placeholder];
                } else {
                    $pattern = '[^/]+';
                }

                return $optional ?
                    '(?:/('.$pattern.'))?' :
                    '/('.$pattern.')';
            },
            Router::normalizePath($this->path)
        );

        return '`^'.$path.'$`u';
    }

    /**
     * Processes the route.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ResponseInterface|string The Response or string response.
     */
    abstract protected function process(ServerRequestInterface $request): ResponseInterface|string;
}
