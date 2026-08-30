<?php
declare(strict_types=1);

namespace Fyre\Router;

use Closure;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Exceptions\MethodNotAllowedException;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\Uri;
use Fyre\ORM\Entity;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Exceptions\RouterException;
use Fyre\Router\Routes\ClosureRoute;
use Fyre\Router\Routes\ControllerRoute;
use Fyre\Router\Routes\RedirectRoute;
use Fyre\Utility\EnumHelper;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use UnitEnum;

use function array_map;
use function array_merge;
use function array_pop;
use function array_unique;
use function getservbyname;
use function implode;
use function in_array;
use function preg_match;
use function preg_replace_callback;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

/**
 * Registers routes and dispatches requests.
 */
class Router
{
    use DebugTrait;
    use MacroTrait;

    protected const DEFAULT_METHODS = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    protected Uri|null $baseUri = null;

    /**
     * @var array<string, mixed>[]
     */
    protected array $groups = [];

    protected ServerRequestInterface|null $request = null;

    /**
     * @var array<string, Route>
     */
    protected array $routeAliases = [];

    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * Normalizes a path.
     *
     * Note: This ensures a leading slash and trims surrounding slashes, but does not
     * collapse duplicate slashes inside the path.
     *
     * @param string $path The path.
     * @return string The normalized path.
     */
    public static function normalizePath(string $path): string
    {
        return '/'.trim($path, '/');
    }

    /**
     * Constructs a Router.
     *
     * @param Container $container The Container.
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     * @param RouteLocator $routeLocator The RouteLocator.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        protected ModelRegistry $modelRegistry,
        protected RouteLocator $routeLocator,
        Config $config
    ) {
        $this->baseUri = new Uri($config->get('App.baseUri', ''));
    }

    /**
     * Clears all routes and aliases.
     */
    public function clear(): void
    {
        $this->routes = [];
        $this->routeAliases = [];
    }

    /**
     * Connects a route.
     *
     * Note: Route group settings are applied in stack order (nested groups last), and
     * middleware, placeholders and binding callbacks are merged from outer → inner → route.
     *
     * Note: For controller routes, the destination array must contain a controller class
     * name (not an instance).
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[]|null $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param bool $redirect Whether the route is a redirect.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function connect(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array|null $methods = null,
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        bool $redirect = false,
        array $bindingCallbacks = []
    ): Route {
        $path = static::normalizePath($path);

        if ($destination instanceof Closure) {
            $className = ClosureRoute::class;
        } else if ($redirect) {
            $className = RedirectRoute::class;
        } else {
            $className = ControllerRoute::class;
        }

        $groupPrefixes = [];
        $groupScheme = null;
        $groupHost = null;
        $groupPort = null;
        $groupMiddleware = [];
        $groupPlaceholders = [];
        $groupBindingCallbacks = [];
        $groupAs = [];

        foreach ($this->groups as $group) {
            if ($group['prefix'] && $group['prefix'] !== '/') {
                $groupPrefixes[] = static::normalizePath($group['prefix']);
            }

            if ($group['scheme'] !== null) {
                $groupScheme = $group['scheme'];
            }

            if ($group['host'] !== null) {
                $groupHost = $group['host'];
            }

            if ($group['port'] !== null) {
                $groupPort = $group['port'];
            }

            $groupMiddleware = array_merge($groupMiddleware, (array) $group['middleware']);
            $groupPlaceholders = array_merge($groupPlaceholders, $group['placeholders']);
            $groupBindingCallbacks = array_merge($groupBindingCallbacks, $group['bindingCallbacks']);

            if ($group['as']) {
                $groupAs[] = $group['as'];
            }
        }

        if ($groupPrefixes !== []) {
            $path = implode('', $groupPrefixes).$path |> static::normalizePath(...);
        }

        $scheme ??= $groupScheme;
        $host ??= $groupHost;
        $port ??= $groupPort;

        if ($methods !== null) {
            $methods = array_map(
                strtoupper(...),
                (array) $methods
            ) |> array_unique(...);
        }

        $middleware = array_merge($groupMiddleware, $middleware);
        $placeholders = array_merge($groupPlaceholders, $placeholders);
        $bindingCallbacks = array_merge($groupBindingCallbacks, $bindingCallbacks);

        if ($as && $groupAs !== []) {
            $as = implode('', $groupAs).$as;
        }
        $route = $this->container->build($className, [
            'destination' => $destination,
            'path' => $path,
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'methods' => $methods,
            'middleware' => $middleware,
            'placeholders' => $placeholders,
            'bindingCallbacks' => $bindingCallbacks,
        ]);

        $this->routes[] = $route;

        if ($as) {
            $this->routeAliases[$as] = $route;
        }

        return $route;
    }

    /**
     * Connects a DELETE route.
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[] $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function delete(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $methods = ['DELETE'],
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            bindingCallbacks: $bindingCallbacks
        );
    }

    /**
     * Loads the attribute routes.
     *
     * Note: This expects the discovered route arrays to have keys matching
     * {@see self::connect()} parameter names (so argument unpacking uses named arguments).
     *
     * @param string[] $namespaces The namespaces.
     */
    public function discoverRoutes(array $namespaces = []): void
    {
        $routes = $this->routeLocator->discover($namespaces);

        foreach ($routes as $route) {
            $this->connect(...$route);
        }
    }

    /**
     * Connects a GET route.
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[] $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function get(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $methods = ['GET'],
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            bindingCallbacks: $bindingCallbacks
        );
    }

    /**
     * Returns the base URI.
     *
     * @return string|null The base URI.
     */
    public function getBaseUri(): string|null
    {
        return $this->baseUri ?
            $this->baseUri->getUri() :
            null;
    }

    /**
     * Creates a group of routes.
     *
     * Note: Groups can be nested; options cascade and are applied to all routes connected
     * inside the callback.
     *
     * @param Closure(Router): void $callback The callback to define routes.
     * @param string|null $prefix The route prefix.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     */
    public function group(
        Closure $callback,
        string|null $prefix = null,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): void {
        $this->groups[] = [
            'prefix' => $prefix,
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'middleware' => $middleware,
            'placeholders' => $placeholders,
            'as' => $as,
            'bindingCallbacks' => $bindingCallbacks,
        ];

        try {
            $this->container->call($callback, ['router' => $this]);
        } finally {
            array_pop($this->groups);
        }
    }

    /**
     * Parses a ServerRequest.
     *
     * Note: This sets the `relativePath` request attribute and stores the matched request
     * internally for subsequent URL generation.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ServerRequestInterface A new ServerRequest.
     *
     * @throws MethodNotAllowedException If the path matched but the method was not allowed.
     * @throws NotFoundException If the route was not found.
     */
    public function parseRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath() |> static::normalizePath(...);

        if ($this->baseUri) {
            $basePath = $this->baseUri->getPath() |> static::normalizePath(...);

            if ($basePath !== '/' && ($path === $basePath || str_starts_with($path, $basePath.'/'))) {
                $path = substr($path, strlen($basePath)) |> static::normalizePath(...);
            }
        }

        $request = $request->withAttribute('relativePath', $path);
        $method = $request->getMethod();
        $allowedMethods = [];
        $headRequest = null;

        foreach ($this->routes as $route) {
            $newRequest = $route->matchRequest($request);

            if ($newRequest === null) {
                continue;
            }

            $methods = $route->getMethods() ?? static::DEFAULT_METHODS;

            if (in_array($method, $methods, true)) {
                return $this->request = $newRequest;
            }

            if ($method === 'HEAD' && in_array('GET', $methods, true)) {
                $headRequest ??= $newRequest;
            }

            $allowedMethods = array_merge($allowedMethods, $methods);
        }

        if ($headRequest !== null) {
            return $this->request = $headRequest;
        }

        if ($allowedMethods === []) {
            throw new NotFoundException(sprintf(
                'No route found for the path `%s`.',
                $path
            ));
        }

        if (in_array('GET', $allowedMethods, true)) {
            $allowedMethods[] = 'HEAD';
        }

        $allowedMethods = array_unique($allowedMethods);

        throw new MethodNotAllowedException(headers: [
            'Allow' => implode(', ', $allowedMethods),
        ]);
    }

    /**
     * Connects a PATCH route.
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[] $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function patch(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $methods = ['PATCH'],
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            bindingCallbacks: $bindingCallbacks
        );
    }

    /**
     * Connects a POST route.
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[] $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function post(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $methods = ['POST'],
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            bindingCallbacks: $bindingCallbacks
        );
    }

    /**
     * Connects a PUT route.
     *
     * @param string $path The route path.
     * @param array{0: class-string, 1?: string}|Closure|string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[] $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @param array<string, Closure> $bindingCallbacks The route binding callbacks.
     * @return Route The Route.
     */
    public function put(
        string $path,
        array|Closure|string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array $methods = ['PUT'],
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null,
        array $bindingCallbacks = []
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            bindingCallbacks: $bindingCallbacks
        );
    }

    /**
     * Connects a redirect route.
     *
     * @param string $path The route path.
     * @param string $destination The route destination.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param string[]|null $methods The route methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The route middleware.
     * @param array<string, string> $placeholders The route placeholders.
     * @param string|null $as The route alias.
     * @return Route The Route.
     */
    public function redirect(
        string $path,
        string $destination,
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array|null $methods = null,
        array $middleware = [],
        array $placeholders = [],
        string|null $as = null
    ): Route {
        return $this->connect(
            $path,
            $destination,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders,
            $as,
            true
        );
    }

    /**
     * Generates a URL for a named route.
     *
     * Note: The special argument keys `?` and `#` are used for query parameters and
     * fragment respectively. Placeholder values may be {@see Entity} instances, in which
     * case the route key field is used.
     *
     * @param string $name The name.
     * @param array<string, mixed> $arguments The route arguments.
     * @param string|null $scheme The route scheme.
     * @param string|null $host The route host.
     * @param int|null $port The route port.
     * @param bool|null $full Whether to use a full URL.
     * @return string The URL.
     *
     * @throws RouterException If the route alias does not exist, required arguments are missing, or a parameter value is invalid.
     */
    public function url(
        string $name,
        array $arguments = [],
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        bool|null $full = null
    ): string {
        if (!isset($this->routeAliases[$name])) {
            throw new RouterException(sprintf(
                'Route alias `%s` does not exist.',
                $name
            ));
        }

        $query = $arguments['?'] ?? null;
        $fragment = $arguments['#'] ?? null;

        unset($arguments['?']);
        unset($arguments['#']);

        $route = $this->routeAliases[$name];

        if ($host !== null) {
            $host = new Uri()
                ->withHost($host)
                ->getHost();
        }

        $scheme ??= $route->getScheme();
        $host ??= $route->getHost();
        $port ??= $route->getPort();

        $destination = $route->getPath();
        $placeholders = $route->getPlaceholders();

        $destination = (string) preg_replace_callback(Route::PLACEHOLDER_REGEXP, function(array $match) use ($arguments, $placeholders): string {
            [$name, $field, $optional] = Route::parsePlaceholder($match[2]);

            $value = $arguments[$name] ?? null;

            if (!$optional && $value === null) {
                throw new RouterException(sprintf(
                    'Router parameter `%s` is missing.',
                    $name
                ));
            }

            if ($value === null) {
                return '';
            }

            if ($value instanceof Entity) {
                $Model = ((string) $value->getModelAlias()) |> $this->modelRegistry->use(...);
                $field ??= $Model->getRouteKey();

                $value = $value->get($field);
            }

            if ($value instanceof UnitEnum) {
                $value = EnumHelper::normalizeValue($value);
            }

            $value = (string) $value;

            $pattern = $placeholders[$name] ?? '([^/]+)';

            if (!preg_match('`^'.$pattern.'\z`u', $value)) {
                throw new RouterException(sprintf(
                    'Route parameter `%s` is not valid.',
                    $name
                ));
            }

            return $match[1].$value;
        }, $destination);

        if ($this->baseUri) {
            $basePath = $this->baseUri->getPath() |> static::normalizePath(...);

            if ($basePath !== '/') {
                $destination = $basePath.$destination;
            }
        }

        if ($this->request) {
            $requestUri = $this->request->getUri();

            $scheme ??= $requestUri->getScheme();
            $host ??= $requestUri->getHost();
            $port ??= $requestUri->getPort();

            $full ??=
                $scheme !== $requestUri->getScheme() ||
                $host !== $requestUri->getHost() ||
                (
                    $port !== $requestUri->getPort() &&
                    $port !== getservbyname($requestUri->getScheme(), 'tcp')
                );
        } else {
            $full ??= true;
        }

        $uri = new Uri();

        if ($full) {
            if ($this->baseUri) {
                $scheme ??= $this->baseUri->getScheme();
                $host ??= $this->baseUri->getHost();
                $port ??= $this->baseUri->getPort();
            }

            $scheme ??= '';
            $host ??= '';

            $uri = $uri
                ->withScheme($scheme)
                ->withHost($host)
                ->withPort($port);
        }

        $uri = $uri->withPath($destination);

        if ($query) {
            $uri = $uri->withQueryParams($query);
        }

        if ($fragment) {
            $uri = $uri->withFragment($fragment);
        }

        return $uri->getUri();
    }
}
