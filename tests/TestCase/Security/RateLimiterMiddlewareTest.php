<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\File\FileCacher;
use Fyre\Core\Config;
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
use Fyre\Security\RateLimiter\FixedWindowRateLimiter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Mock\Controllers\TestController;

use function class_uses;
use function glob;
use function mkdir;
use function rmdir;
use function sleep;
use function time;
use function unlink;
use function usleep;

final class RateLimiterMiddlewareTest extends TestCase
{
    protected CacheManager $cacheManager;

    protected Container $container;

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function argumentCostProvider(): array
    {
        return [
            'positive cost' => ['throttle:6,5,2', 1, '4'],
            'zero cost' => ['throttle:6,5,0', 5, '6'],
        ];
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function strategyProvider(): array
    {
        return [
            'default' => [null],
            'fixed window' => ['fixedWindow'],
            'token bucket' => ['tokenBucket'],
        ];
    }

    #[DataProvider('argumentCostProvider')]
    public function testArgumentCost(string $middleware, int $cost, string $expected): void
    {
        $middlewareRegistry = $this->container->build(MiddlewareRegistry::class);
        $middlewareRegistry->map(
            'throttle',
            static fn(Container $container): RateLimiterMiddleware => $container->build(RateLimiterMiddleware::class, [
                'options' => [
                    'limit' => 10,
                    'window' => 10,
                    'cost' => $cost,
                ],
            ])
        );

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

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
            $expected,
            $response->getHeaderLine('X-RateLimit-Remaining')
        );
    }

    public function testArgumentLimit(): void
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
        $queue->add('throttle:6');

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
            '6',
            $response->getHeaderLine('X-RateLimit-Limit')
        );
    }

    public function testArgumentWindow(): void
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
        $queue->add('throttle:10,5');

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

        $before = time();
        $response = $handler->handle($request);
        $after = time();
        $reset = (int) $response->getHeaderLine('X-RateLimit-Reset');

        $this->assertGreaterThanOrEqual(
            $before - ($before % 5) + 5,
            $reset
        );

        $this->assertLessThanOrEqual(
            $after - ($after % 5) + 5,
            $reset
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
            '5',
            $response->getHeaderLine('X-RateLimit-Remaining')
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
        for ($i = 1; $i <= 6; $i++) {
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

            try {
                $response = $handler->handle($request);
            } catch (TooManyRequestsException) {
                $this->assertSame(6, $i);

                return;
            }

            $this->assertSame(
                204,
                $response->getStatusCode()
            );
        }

        $this->fail('The sixth request was not rejected.');
    }

    public function testErrorMessage(): void
    {
        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessageIs('Rate limit exceeded');

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 5,
                'cost' => 6,
                'window' => 60,
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $handler->handle($request);
    }

    public function testErrorRetryAfter(): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 5,
                'cost' => 6,
                'window' => 60,
            ],
        ]);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        try {
            $handler->handle($request);
        } catch (TooManyRequestsException $e) {
            $retryAfter = (int) ($e->getHeaders()['Retry-After'] ?? 0);

            $this->assertGreaterThanOrEqual(
                1,
                $retryAfter
            );

            $this->assertLessThanOrEqual(
                60,
                $retryAfter
            );

            return;
        }

        $this->fail('A request exceeding the limit was not rejected.');
    }

    public function testFixedWindowLimitAcrossSeconds(): void
    {
        $limiter = $this->container->build(FixedWindowRateLimiter::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
            ],
        ]);
        $request = $this->container->build(ServerRequest::class);

        $first = $limiter->checkLimit($request);

        $this->assertTrue($first['allowed']);

        sleep(1);

        $second = $limiter->checkLimit($request);

        // If the first request landed in the final second, check the new window instead.
        if ($second['reset'] !== $first['reset']) {
            $first = $second;

            $this->assertTrue($first['allowed']);

            sleep(1);

            $second = $limiter->checkLimit($request);
        }

        $this->assertFalse($second['allowed']);
        $this->assertSame(0, $second['remaining']);
        $this->assertSame($first['reset'], $second['reset']);
        $this->assertSame(0, $second['reset'] % 60);
    }

    public function testFixedWindowLimitResets(): void
    {
        $limiter = $this->container->build(FixedWindowRateLimiter::class, [
            'options' => [
                'limit' => 1,
                'window' => 1,
            ],
        ]);
        $request = $this->container->build(ServerRequest::class);

        $first = $limiter->checkLimit($request);

        $this->assertTrue($first['allowed']);
        $this->assertSame(0, $first['remaining']);

        sleep(1);

        $second = $limiter->checkLimit($request);

        $this->assertTrue($second['allowed']);
        $this->assertSame(0, $second['remaining']);
        $this->assertGreaterThan($first['reset'], $second['reset']);
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

    public function testInvalidCost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Rate limiter cost must not be negative.');

        $limiter = $this->container->build(FixedWindowRateLimiter::class, [
            'options' => [
                'cost' => static fn(): int => -1,
            ],
        ]);
        $request = $this->container->build(ServerRequest::class);

        $limiter->checkLimit($request);
    }

    public function testInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Rate limiter limit must be greater than 0.');

        $limiter = $this->container->build(FixedWindowRateLimiter::class, [
            'options' => [
                'limit' => 0,
            ],
        ]);
        $request = $this->container->build(ServerRequest::class);

        $limiter->checkLimit($request);
    }

    public function testInvalidWindow(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Rate limiter window must be greater than 0.');

        $limiter = $this->container->build(FixedWindowRateLimiter::class);
        $request = $this->container->build(ServerRequest::class);

        $limiter->checkLimit($request, window: 0);
    }

    public function testIpIdentifierIgnoresForwardedHeaderByDefault(): void
    {
        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessageIs('Rate limit exceeded');

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
                'identifier' => 'ip',
            ],
        ]);

        $request1 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.10',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $request2 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.20',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $queue1 = new MiddlewareQueue();
        $queue1->add($middleware);
        $handler1 = $this->container->build(RequestHandler::class, ['queue' => $queue1]);

        $this->assertSame(
            204,
            $handler1->handle($request1)->getStatusCode()
        );

        $queue2 = new MiddlewareQueue();
        $queue2->add($middleware);
        $handler2 = $this->container->build(RequestHandler::class, ['queue' => $queue2]);
        $handler2->handle($request2);
    }

    public function testIpIdentifierIgnoresForwardedHeaderForUntrustedProxy(): void
    {
        $this->expectException(TooManyRequestsException::class);
        $this->expectExceptionMessageIs('Rate limit exceeded');

        $this->container->use(Config::class)
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['10.0.0.1']);

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
                'identifier' => 'ip',
            ],
        ]);

        $request1 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.10',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $request2 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.20',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $queue1 = new MiddlewareQueue();
        $queue1->add($middleware);
        $handler1 = $this->container->build(RequestHandler::class, ['queue' => $queue1]);

        $this->assertSame(
            204,
            $handler1->handle($request1)->getStatusCode()
        );

        $queue2 = new MiddlewareQueue();
        $queue2->add($middleware);
        $handler2 = $this->container->build(RequestHandler::class, ['queue' => $queue2]);
        $handler2->handle($request2);
    }

    public function testIpIdentifierStopsAtFirstUntrustedProxy(): void
    {
        $this->container->use(Config::class)
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['127.0.0.1']);

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
                'identifier' => 'ip',
            ],
        ]);

        $request1 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '198.51.100.10, 203.0.113.10',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $request2 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '198.51.100.10, 203.0.113.20',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $queue1 = new MiddlewareQueue();
        $queue1->add($middleware);
        $handler1 = $this->container->build(RequestHandler::class, ['queue' => $queue1]);

        $queue2 = new MiddlewareQueue();
        $queue2->add($middleware);
        $handler2 = $this->container->build(RequestHandler::class, ['queue' => $queue2]);

        $this->assertSame(
            204,
            $handler1->handle($request1)->getStatusCode()
        );

        $this->assertSame(
            204,
            $handler2->handle($request2)->getStatusCode()
        );
    }

    public function testIpIdentifierUsesForwardedHeaderForTrustedProxy(): void
    {
        $this->container->use(Config::class)
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['127.0.0.1']);

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
                'identifier' => 'ip',
            ],
        ]);

        $request1 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.10, 127.0.0.1',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $request2 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '203.0.113.20, 127.0.0.1',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $queue1 = new MiddlewareQueue();
        $queue1->add($middleware);
        $handler1 = $this->container->build(RequestHandler::class, ['queue' => $queue1]);

        $queue2 = new MiddlewareQueue();
        $queue2->add($middleware);
        $handler2 = $this->container->build(RequestHandler::class, ['queue' => $queue2]);

        $this->assertSame(
            204,
            $handler1->handle($request1)->getStatusCode()
        );

        $this->assertSame(
            204,
            $handler2->handle($request2)->getStatusCode()
        );
    }

    public function testIpIdentifierUsesLastForwardedIpWithoutTrustedProxies(): void
    {
        $this->container->use(Config::class)->set('App.trustProxy', true);

        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 1,
                'window' => 60,
                'identifier' => 'ip',
            ],
        ]);

        $request1 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '198.51.100.10, 203.0.113.10',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $request2 = $this->container->build(ServerRequest::class, [
            'options' => [
                'headers' => [
                    'X-Forwarded-For' => '198.51.100.10, 203.0.113.20',
                ],
                'server' => [
                    'REMOTE_ADDR' => '127.0.0.1',
                ],
            ],
        ]);

        $queue1 = new MiddlewareQueue();
        $queue1->add($middleware);
        $handler1 = $this->container->build(RequestHandler::class, ['queue' => $queue1]);

        $queue2 = new MiddlewareQueue();
        $queue2->add($middleware);
        $handler2 = $this->container->build(RequestHandler::class, ['queue' => $queue2]);

        $this->assertSame(
            204,
            $handler1->handle($request1)->getStatusCode()
        );

        $this->assertSame(
            204,
            $handler2->handle($request2)->getStatusCode()
        );
    }

    #[DataProvider('strategyProvider')]
    public function testLimitHeader(string|null $strategy): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => $strategy,
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
            '10',
            $response->getHeaderLine('X-RateLimit-Limit')
        );
    }

    #[DataProvider('strategyProvider')]
    public function testRemainingHeader(string|null $strategy): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => $strategy,
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
            '9',
            $response->getHeaderLine('X-RateLimit-Remaining')
        );
    }

    #[DataProvider('strategyProvider')]
    public function testRequest(string|null $strategy): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => $strategy,
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
    }

    #[DataProvider('strategyProvider')]
    public function testResetHeader(string|null $strategy): void
    {
        $middleware = $this->container->build(RateLimiterMiddleware::class, [
            'options' => [
                'limit' => 10,
                'window' => 10,
                'strategy' => $strategy,
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

        $before = time();
        $response = $handler->handle($request);

        $this->assertGreaterThan(
            $before,
            (int) $response->getHeaderLine('X-RateLimit-Reset')
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
        $this->container->singleton(Config::class);

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
        $files = glob('cache/ratelimiter_*') ?: [];

        foreach ($files as $file) {
            @unlink($file);
        }

        @rmdir('cache');
    }
}
