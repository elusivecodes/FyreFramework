<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Http\ClientResponse;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Router\Middleware\RouterMiddleware;
use Fyre\Router\RouteHandler;
use Fyre\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Mock\Controllers\HomeController;
use Tests\Mock\Http\Middleware\RouteArgsMiddleware;

trait MiddlewareTestTrait
{
    public function testGroupMiddleware(): void
    {
        $results = [];

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router) use (&$results): void {
            $router->connect('test', HomeController::class, middleware: [
                static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$results): ResponseInterface {
                    $results[] = 'test2';

                    return $handler->handle($request);
                },
            ]);
        }, middleware: [
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$results): ResponseInterface {
                $results[] = 'test1';

                return $handler->handle($request);
            },
        ]);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertSame(
            [
                'test1',
                'test2',
            ],
            $results
        );
    }

    public function testGroupMiddlewareDeep(): void
    {
        $results = [];

        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router) use (&$results): void {
            $router->group(static function(Router $router) use (&$results): void {
                $router->connect('test', HomeController::class, middleware: [
                    static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$results): ResponseInterface {
                        $results[] = 'test3';

                        return $handler->handle($request);
                    },
                ]);
            }, middleware: [
                static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$results): ResponseInterface {
                    $results[] = 'test2';

                    return $handler->handle($request);
                },
            ]);
        }, middleware: [
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$results): ResponseInterface {
                $results[] = 'test1';

                return $handler->handle($request);
            },
        ]);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertSame(
            [
                'test1',
                'test2',
                'test3',
            ],
            $results
        );
    }

    public function testMiddleware(): void
    {
        $ran = false;

        $router = $this->container->use(Router::class);

        $router->connect('test', HomeController::class, middleware: [
            static function(ServerRequestInterface $request, RequestHandlerInterface $handler) use (&$ran): ResponseInterface {
                $ran = true;

                return $handler->handle($request);
            },
        ]);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testMiddlewareArgs(): void
    {
        $middlewareRegistry = $this->container->use(MiddlewareRegistry::class);

        $middlewareRegistry->map('test', RouteArgsMiddleware::class);

        $router = $this->container->use(Router::class);

        $router->connect('test/{a}/{b}', HomeController::class, middleware: [
            'test:a,b',
        ]);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/2/1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            '[
    "2",
    "1"
]',
            $response->getBody()->getContents()
        );
    }
}
