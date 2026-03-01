<?php
declare(strict_types=1);

namespace Fyre\Router;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Core\Loader;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Router\Attributes\Hidden;
use Fyre\Router\Attributes\Route;
use Fyre\Utility\Inflector;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use RegexIterator;

use function array_filter;
use function array_map;
use function array_merge;
use function class_exists;
use function explode;
use function implode;
use function is_a;
use function ltrim;
use function mb_strlen;
use function str_ends_with;
use function str_replace;
use function strcmp;
use function strlen;
use function substr;
use function usort;

use const DIRECTORY_SEPARATOR;

/**
 * Discovers routes from controllers using route attributes.
 */
class RouteLocator
{
    use DebugTrait;

    protected const CACHE_KEY = '_routes';

    /**
     * @var array<string, array<string, mixed>[]>
     */
    protected array $routes = [];

    /**
     * Constructs a RouteLocator.
     *
     * @param Loader $loader The Loader.
     * @param CacheManager $cacheManager The CacheManager.
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        protected Loader $loader,
        protected CacheManager $cacheManager,
        protected Inflector $inflector
    ) {}

    /**
     * Clears discovered routes (including cache, if configured).
     */
    public function clear(): void
    {
        $this->routes = [];

        $cache = $this->getCache();

        if ($cache) {
            $cache->clear();
        }
    }

    /**
     * Discovers all routes for namespaces.
     *
     * Routes are cached per namespace when the `_routes` cache config is present.
     *
     * Note: Returned routes are sorted by most-specific path first.
     *
     * @param string[] $namespaces The namespaces.
     * @return array<string, mixed>[] The available routes.
     */
    public function discover(array $namespaces = []): array
    {
        $cache = $this->getCache();

        $routes = [];
        foreach ($namespaces as $namespace) {
            if (isset($this->routes[$namespace])) {
                $routes[] = $this->routes[$namespace];
            } else if ($cache) {
                $cacheKey = str_replace('\\', '.', $namespace);
                $routes[] = $cache->remember($cacheKey, fn(): array => $this->findRoutes($namespace));
            } else {
                $routes[] = $this->findRoutes($namespace);
            }
        }

        $routes = array_merge([], ...$routes);

        usort(
            $routes,
            static fn(array $a, array $b): int => mb_strlen($b['path']) <=> mb_strlen($a['path']) ?:
                    strcmp($a['path'], $b['path']) ?:
                    strcmp($a['destination'][0], $b['destination'][0]) ?:
                    strcmp($a['destination'][1], $b['destination'][1])
        );

        return $routes;
    }

    /**
     * Returns the Cacher.
     *
     * @return Cacher|null The Cacher instance or null if caching is not configured.
     */
    public function getCache(): Cacher|null
    {
        return $this->cacheManager->hasConfig(static::CACHE_KEY) ?
            $this->cacheManager->use(static::CACHE_KEY) :
            null;
    }

    /**
     * Finds all routes in a namespace.
     *
     * Discovers controllers/actions by scanning PHP files in the namespace folders, and
     * uses {@see Route} attributes (and {@see Hidden}) to build route metadata.
     *
     * Note: When no route path is provided, paths and aliases are derived from the
     * controller/method names, and optional method parameters become `{param?}` path
     * segments.
     *
     * @param string $namespace The namespace.
     * @return array<string, mixed>[] The routes.
     */
    protected function findRoutes(string $namespace): array
    {
        $namespace = Loader::normalizeNamespace($namespace);

        $folders = $this->loader->findFolders($namespace);

        $routes = [];

        foreach ($folders as $folder) {
            $folderLength = strlen($folder);

            $directory = new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            $iterator = new RegexIterator($iterator, '/\.php$/');

            foreach ($iterator as $item) {
                if ($item->isDir()) {
                    continue;
                }

                $fullNamespace = $namespace;
                $subNamespace = '';

                $directory = $item->getPathInfo();
                $directoryPath = $directory->getPathname();

                if ($directoryPath !== $folder) {
                    $directoryPath = substr($directoryPath, $folderLength);
                    $subNamespace = str_replace(DIRECTORY_SEPARATOR, '\\', $directoryPath);
                    $fullNamespace .= ltrim($subNamespace, '\\').'\\';
                }

                $controllerName = $item->getBasename('.php');

                $className = $fullNamespace.$controllerName;

                if (!class_exists($className)) {
                    continue;
                }

                $loadedClasses[$className] = true;

                $reflection = new ReflectionClass($className);

                if ($reflection->isAbstract()) {
                    continue;
                }

                $classAttribute = $reflection->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

                if ($classAttribute && is_a($classAttribute->getName(), Hidden::class, true)) {
                    continue;
                }

                if (str_ends_with($controllerName, 'Controller') && $controllerName !== 'Controller') {
                    $controllerName = substr($controllerName, 0, -10);
                }

                if ($classAttribute) {
                    $instance = $classAttribute->newInstance();
                    $routeDefaults = $instance->getRoute();
                } else {
                    $routeDefaults = [];
                }

                $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method) {
                    $methodName = $method->getName();
                    $methodAttribute = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

                    if ($methodAttribute && is_a($methodAttribute->getName(), Hidden::class, true)) {
                        continue;
                    }

                    if ($methodAttribute) {
                        $instance = $methodAttribute->newInstance();
                        $route = $instance->getRoute();
                    } else {
                        $route = [];
                    }

                    if (isset($route['path'])) {
                        $data['path'] = $route['path'];
                    } else {
                        if (isset($routeDefaults['path'])) {
                            $parts = explode('/', $routeDefaults['path']) |> array_filter(...);
                        } else {
                            $parts = explode('\\', $subNamespace) |> array_filter(...);
                            $parts[] = $controllerName;

                            $parts = array_map(
                                $this->inflector->dasherize(...),
                                $parts
                            );
                        }

                        if ($methodName !== 'index') {
                            $parts[] = $this->inflector->dasherize($methodName);
                        }

                        $params = $method->getParameters();

                        foreach ($params as $param) {
                            $parts[] = '{'.
                                $param->getName().
                                ($param->isOptional() ? '?' : '').
                                '}';
                        }

                        $data['path'] = implode('/', $parts);
                    }

                    $data['destination'] = [$className, $methodName];
                    $data['scheme'] = $route['scheme'] ?? $routeDefaults['scheme'] ?? null;
                    $data['host'] = $route['host'] ?? $routeDefaults['host'] ?? null;
                    $data['port'] = $route['port'] ?? $routeDefaults['port'] ?? null;
                    $data['methods'] = $route['methods'] ?? $routeDefaults['methods'] ?? match ($methodName) {
                        'create' => ['POST'],
                        'delete' => ['DELETE'],
                        'update' => ['PATCH', 'PUT'],
                        default => ['GET']
                    };
                    $data['middleware'] = array_merge($routeDefaults['middleware'] ?? [], $route['middleware'] ?? []);
                    $data['placeholders'] = array_merge($routeDefaults['placeholders'] ?? [], $route['placeholders'] ?? []);

                    if (isset($route['as'])) {
                        $data['as'] = $route['as'];
                    } else if (isset($routeDefaults['as'])) {
                        $data['as'] = $routeDefaults['as'].'.'.$this->inflector->dasherize($methodName);
                    } else {
                        if ($subNamespace) {
                            $parts = explode('\\', $subNamespace) |> array_filter(...);
                        } else {
                            $parts = [];
                        }
                        $parts[] = $controllerName;
                        $parts[] = $methodName;
                        $parts = array_map(
                            $this->inflector->dasherize(...),
                            $parts
                        );
                        $data['as'] = implode('.', $parts);
                    }

                    $routes[] = $data;
                }
            }
        }

        return $routes;
    }
}
