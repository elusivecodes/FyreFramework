<?php
declare(strict_types=1);

namespace Tests\TestCase\Router;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Middleware\RouterMiddleware;
use Fyre\Router\Middleware\SubstituteBindingsMiddleware;
use Fyre\Router\RouteHandler;
use Fyre\Router\Router;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\HomeController;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

use function class_uses;

final class RouterMiddlewareTest extends TestCase
{
    protected Container $container;

    protected Router $router;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(RouterMiddleware::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(SubstituteBindingsMiddleware::class)
        );
    }

    public function testProcessClosureRoute(): void
    {
        $ran = false;

        $destination = static function() use (&$ran): string {
            $ran = true;

            return '';
        };

        $this->router->connect('test', $destination);

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

    public function testProcessControllerRoute(): void
    {
        $this->router->connect('test', HomeController::class);

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
    }

    public function testProcessRedirectRoute(): void
    {
        $this->router->redirect('test', 'https://test.com/');

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

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            302,
            $response->getStatusCode()
        );

        $this->assertSame(
            'https://test.com/',
            $response->getHeaderLine('Location')
        );
    }

    public function testProcessRouteBackedEnumParam(): void
    {
        $ran = false;

        $destination = function(Status $status) use (&$ran): string {
            $ran = true;

            $this->assertSame(
                Status::Draft,
                $status
            );

            return '';
        };

        $this->router->connect('test/{status}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/draft',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testProcessRouteBackedEnumParamInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not Found');

        $destination = static function(Status $status): string {
            return '';
        };

        $this->router->connect('test/{status}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/invalid',
                ],
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessRouteUnitEnumParam(): void
    {
        $ran = false;

        $destination = function(State $state) use (&$ran): string {
            $ran = true;

            $this->assertSame(
                State::Draft,
                $state
            );

            return '';
        };

        $this->router->connect('test/{state}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/Draft',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(ModelRegistry::class);
        $this->container->singleton(EntityLocator::class);
        $this->container->singleton(Router::class);

        $this->router = $this->container->use(Router::class);
    }
}
