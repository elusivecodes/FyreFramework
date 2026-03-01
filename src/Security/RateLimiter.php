<?php
declare(strict_types=1);

namespace Fyre\Security;

use Closure;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Router\Routes\ControllerRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_replace_recursive;
use function hash;
use function implode;

/**
 * Provides shared rate limiting helpers and configuration.
 *
 * Concrete implementations determine how rate limit state is tracked, while this base class
 * handles identifier generation, cost calculation, header injection, and cache setup.
 *
 * @phpstan-type RateLimitData array{allowed: bool, limit: int, remaining: int, reset: int}
 */
abstract class RateLimiter
{
    use DebugTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'cacheConfig' => 'ratelimiter',
        'limit' => 60,
        'window' => 60,
        'cost' => 1,
        'message' => 'Rate limit exceeded',
        'identifier' => ['ip'],
        'skipCheck' => null,
    ];

    protected string $cacheConfig;

    protected Closure|int $cost;

    /**
     * @var Closure|string[]
     */
    protected array|Closure $identifier;

    protected int $limit;

    protected string $message;

    protected Closure|null $skipCheck;

    protected int $window;

    /**
     * Constructs a RateLimiter.
     *
     * @param Container $container The Container.
     * @param CacheManager $cacheManager The CacheManager.
     * @param array<string, mixed> $options The RateLimiter options.
     */
    public function __construct(
        protected Container $container,
        protected CacheManager $cacheManager,
        array $options = []
    ) {
        $options = array_replace_recursive(static::$defaults, $options);

        $this->cacheConfig = $options['cacheConfig'];
        $this->limit = $options['limit'];
        $this->window = $options['window'];
        $this->cost = $options['cost'];
        $this->message = $options['message'];
        $this->identifier = $options['identifier'] instanceof Closure ?
            $options['identifier'] :
            (array) $options['identifier'];
        $this->skipCheck = $options['skipCheck'];

        if (!$this->cacheManager->hasConfig($this->cacheConfig)) {
            $this->cacheManager->setConfig($this->cacheConfig, [
                'className' => FileCacher::class,
                'prefix' => $this->cacheConfig.':',
            ]);
        }
    }

    /**
     * Adds rate limit headers to a Response.
     *
     * Note: Headers are only added when rate limit data is available.
     *
     * @param ResponseInterface $response The Response.
     * @param RateLimitData $data The rate limit data.
     * @return ResponseInterface The new Response.
     */
    public function addHeaders(ResponseInterface $response, array $data): ResponseInterface
    {
        if ($data === []) {
            return $response;
        }

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $data['limit'])
            ->withHeader('X-RateLimit-Remaining', (string) $data['remaining'])
            ->withHeader('X-RateLimit-Reset', (string) $data['reset']);
    }

    /**
     * Checks the rate limit for a request.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @param int|null $limit The request limit.
     * @param int|null $window The time window in seconds.
     * @param int|null $cost The request cost.
     * @return RateLimitData The rate limit data.
     */
    abstract public function checkLimit(ServerRequestInterface $request, int|null $limit, int|null $window, int|null $cost = 1): array;

    /**
     * Returns the error message.
     *
     * @return string The error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Checks whether rate limiting should be skipped for the request.
     *
     * Note: This uses the configured `skipCheck` callback (if any).
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return bool Whether rate limiting should be skipped for the request.
     */
    public function shouldSkip(ServerRequestInterface $request): bool
    {
        return $this->skipCheck && $this->container->call($this->skipCheck, ['request' => $request]) === true;
    }

    /**
     * Generates a cache key.
     *
     * @param string $identifier The identifier.
     * @return string The cache key.
     */
    protected function generateKey(string $identifier): string
    {
        return 'rate_limit_'.hash('xxh3', $identifier);
    }

    /**
     * Returns the cost of the Request.
     *
     * Note: Cost may be a fixed integer or computed via a callback.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return int The cost.
     */
    protected function getCost(ServerRequestInterface $request): int
    {
        if ($this->cost instanceof Closure) {
            return (int) $this->container->call($this->cost, ['request' => $request]);
        }

        return $this->cost;
    }

    /**
     * Returns the identifier.
     *
     * Note: Identifiers can be built from multiple sources (e.g. `ip`, `route`, `user`) and
     * are joined to form a single cache key.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return string The identifier.
     */
    protected function getIdentifier(ServerRequestInterface $request): string
    {
        if ($this->identifier instanceof Closure) {
            return $this->container->call($this->identifier, ['request' => $request]);
        }

        $identifiers = [];

        foreach ($this->identifier as $identifier) {
            switch ($identifier) {
                case 'ip':
                    $identifiers[] = $this->getIpIdentifier($request);
                    break;
                case 'route':
                    $identifiers[] = $this->getRouteIdentifier($request);
                    break;
                case 'user':
                    $identifiers[] = $this->getUserIdentifier($request);
                    break;
            }
        }

        return implode('_', $identifiers);
    }

    /**
     * Returns the IP identifier.
     *
     * Note: Prefers `HTTP_X_FORWARDED_FOR` when present, falling back to `REMOTE_ADDR`.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return string The IP identifier.
     */
    protected function getIpIdentifier(ServerRequestInterface $request): string
    {
        $params = $request->getServerParams();

        return $params['HTTP_X_FORWARDED_FOR'] ?? $params['REMOTE_ADDR'] ?? '';
    }

    /**
     * Returns the route identifier.
     *
     * Note: When available, controller routes use `Controller::action` as the identifier
     * prefix. The client IP is always included.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return string The route identifier.
     */
    protected function getRouteIdentifier(ServerRequestInterface $request): string
    {
        $route = $request->getAttribute('route');

        if ($route && $route instanceof ControllerRoute) {
            $identifier = $route->getController().'::'.$route->getAction();
        } else {
            $identifier = 'unknown';
        }

        return $identifier.'_'.$this->getIpIdentifier($request);
    }

    /**
     * Returns the user identifier.
     *
     * Note: When a user is not available, this falls back to the IP identifier.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return string The user identifier.
     */
    protected function getUserIdentifier(ServerRequestInterface $request): string
    {
        $user = $request->getAttribute('user');

        if (isset($user->id)) {
            return 'user_'.$user->id;
        }

        return $this->getIpIdentifier($request);
    }
}
