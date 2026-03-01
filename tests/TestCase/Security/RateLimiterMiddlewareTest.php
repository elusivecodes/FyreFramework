<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Exceptions\TooManyRequestsException;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Router\Routes\ControllerRoute;
use Fyre\Security\Middleware\RateLimiterMiddleware;
use Fyre\Security\RateLimiter;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Mock\Controllers\TestController;

use function class_uses;
use function glob;
use function mkdir;
use function rmdir;
use function time;
use function unlink;
use function usleep;

final class RateLimiterMiddlewareTest extends TestCase
{
    protected CacheManager $cacheManager;

    protected Container $container;

    public function testArguments(): void
    {
        $middlewareRegistry = $this->container->build(MiddlewareRegistry::class);
        $middlewareRegistry->map(
            'throttle',
            static fn(Container $container): RateLimiterMiddleware => $container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 10,
                    'window' => 10,
                ],
            ])
        );

        $queue = new MiddlewareQueue();
        $queue->add('throttle:6,5,2');

        $handler = $this->container->build(RequestHandler::class, [
            'middlewareRegistry' => $middlewareRegistry,
            'queue' => $queue,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertSame(
            204,
            $response->getStatusCode()
        );

        $this->assertSame(
            '6',
            $response->getHeaderLine('X-RateLimit-Limit')
        );

        $this->assertSame(
            '4',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );

        $this->assertGreaterThan(
            time(),
            $response->getHeaderLine('X-RateLimit-Reset')
        );
    }

    public function testCost(): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'cost' => static fn(): int => 5,
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertSame(
            204,
            $response->getStatusCode()
        );

        $this->assertSame(
            '10',
            $response->getHeaderLine('X-RateLimit-Limit')
        );

        $this->assertSame(
            '5',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );

        $this->assertGreaterThan(
            time(),
            $response->getHeaderLine('X-RateLimit-Reset')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(RateLimiter::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(RateLimiterMiddleware::class)
        );
    }

    public function testError(): void
    {
        try {
            for ($i = 0; $i <= 10; $i++) {
                $middleware = $this->container->build(RateLimiterMiddleware::class, [
                    'options' => [
                        'limit' => 5,
                        'window' => 60,
                    ],
                ]);

                $queue = new MiddlewareQueue();
                $queue->add($middleware);

                $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
                $request = $this->container->build(ServerRequest::class, [
                    'options' => [
                        'server' => [
                            'REMOTE_ADDR' => '127.0.0.1',
                        ],
                    ],
                ]);

                $response = $handler->handle($request);

                usleep(100);
            }

            $this->fail();
        } catch (TooManyRequestsException $e) {
            $this->assertSame(
                'Rate limit exceeded',
                $e->getMessage()
            );

            $this->assertGreaterThan(
                1,
                $e->getHeaders()['Retry-After'] ?? 0
            );
        }
    }

    public function testFixedWindowStrategy(): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => 'fixedWindow',
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertSame(
            204,
            $response->getStatusCode()
        );

        $this->assertSame(
            '10',
            $response->getHeaderLine('X-RateLimit-Limit')
        );

        $this->assertSame(
            '9',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );

        $this->assertGreaterThan(
            time(),
            $response->getHeaderLine('X-RateLimit-Reset')
        );
    }

    public function testHeaders(): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertSame(
            204,
            $response->getStatusCode()
        );

        $this->assertSame(
            '10',
            $response->getHeaderLine('X-RateLimit-Limit')
        );

        $this->assertSame(
            '9',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );

        $this->assertGreaterThan(
            time(),
            $response->getHeaderLine('X-RateLimit-Reset')
        );
    }

    public function testIdentifier(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $middleware = $this->container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 5,
                    'window' => 60,
                    'identifier' => static fn(ServerRequestInterface $request): string => 'user'.$i,
                ],
            ]);

            $queue = new MiddlewareQueue();
            $queue->add($middleware);

            $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
            $request = $this->container->build(ServerRequest::class, [
                'options' => [
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
            ]);

            $response = $handler->handle($request);

            usleep(100);
        }

        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    public function testRouteIdentifier(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $middleware = $this->container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 5,
                    'window' => 60,
                    'identifier' => 'route',
                ],
            ]);

            $queue = new MiddlewareQueue();
            $queue->add($middleware);

            $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
            $request = $this->container->build(ServerRequest::class, [
                'options' => [
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
            ]);

            $route = $this->container->build(ControllerRoute::class, [
                'destination' => [TestController::class, 'method'.$i],
                'path' => '',
            ]);
            $request = $request->withAttribute('route', $route);

            $response = $handler->handle($request);

            usleep(100);
        }

        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    public function testSkipCheck(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $middleware = $this->container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 5,
                    'window' => 60,
                    'skipCheck' => static fn(ServerRequestInterface $request): bool => true,
                ],
            ]);

            $queue = new MiddlewareQueue();
            $queue->add($middleware);

            $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
            $request = $this->container->build(ServerRequest::class, [
                'options' => [
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
            ]);

            $response = $handler->handle($request);

            usleep(100);
        }

        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    public function testTokenBucketStrategy(): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => 'tokenBucket',
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertSame(
            204,
            $response->getStatusCode()
        );

        $this->assertSame(
            '10',
            $response->getHeaderLine('X-RateLimit-Limit')
        );

        $this->assertSame(
            '9',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );

        $this->assertGreaterThan(
            time(),
            $response->getHeaderLine('X-RateLimit-Reset')
        );
    }

    public function testUserIdentifier(): void
    {
        for ($i = 0; $i <= 10; $i++) {
            $middleware = $this->container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 5,
                    'window' => 60,
                    'identifier' => 'user',
                ],
            ]);

            $queue = new MiddlewareQueue();
            $queue->add($middleware);

            $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
            $request = $this->container->build(ServerRequest::class, [
                'options' => [
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
            ]);

            $user = (object) ['id' => $i];
            $request = $request->withAttribute('user', $user);

            $response = $handler->handle($request);

            usleep(100);
        }

        $this->assertSame(
            204,
            $response->getStatusCode()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(CacheManager::class);

        $this->cacheManager = $this->container->use(CacheManager::class);

        $this->cacheManager->setConfig('ratelimiter', [
            'className' => FileCacher::class,
            'path' => 'cache',
            'prefix' => 'ratelimiter_',
        ]);

        @mkdir('cache');
    }

    #[Override]
    protected function tearDown(): void
    {
        $files = glob('cache/ratelimiter_*');

        foreach ($files as $file) {
            @unlink($file);
        }

        @rmdir('cache');
    }
}
