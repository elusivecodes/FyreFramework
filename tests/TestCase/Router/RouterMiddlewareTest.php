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
use Psr\Http\Message\ServerRequestInterface;
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
                'uri' => '/test',
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
                'uri' => '/test',
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
                'uri' => '/test',
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
                'uri' => '/test/draft',
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
        $this->expectExceptionMessageIs('Not Found');

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
                'uri' => '/test/invalid',
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessRouteBindingCallbackInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('Not Found');

        $destination = static function(string $value): string {
            return '';
        };

        $this->router->connect('test/{value}', $destination, bindingCallbacks: [
            'value' => static function(string $value): null {
                return null;
            },
        ]);

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
                'uri' => '/test/value',
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessRouteBindingCallbacks(): void
    {
        $ran = false;

        $destination = function(int $a, int $b) use (&$ran): string {
            $ran = true;

            $this->assertSame(
                1,
                $a
            );

            $this->assertSame(
                2,
                $b
            );

            return '';
        };

        $this->router->connect('test/{a}/{b}', $destination, bindingCallbacks: [
            'a' => static function(string $value): int {
                return (int) $value;
            },
            'b' => function(string $value, ServerRequestInterface $request): int {
                $this->assertSame(
                    1,
                    $request->getAttribute('routeArguments')['a']
                );

                return (int) $value;
            },
        ]);

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
                'uri' => '/test/1/2',
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
    }

    public function testProcessRouteParamDefault(): void
    {
        $callbackRan = false;
        $ran = false;

        $destination = function(string $value = 'default') use (&$ran): string {
            $ran = true;

            $this->assertSame(
                'default',
                $value
            );

            return '';
        };

        $this->router->connect('test/{value?}', $destination, bindingCallbacks: [
            'value' => static function(string $value) use (&$callbackRan): string {
                $callbackRan = true;

                return $value;
            },
        ]);

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
                'uri' => '/test',
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertFalse($callbackRan);
        $this->assertTrue($ran);
    }

    public function testProcessRouteParamMissing(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageIs('Not Found');

        $destination = static function(string $value): string {
            return '';
        };

        $this->router->connect('test/{value?}', $destination);

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
                'uri' => '/test',
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessRouteParamNullable(): void
    {
        $ran = false;

        $destination = function(string|null $value) use (&$ran): string {
            $ran = true;

            $this->assertNull($value);

            return '';
        };

        $this->router->connect('test/{value?}', $destination);

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
                'uri' => '/test',
            ],
        ]);

        $this->assertInstanceOf(
            ClientResponse::class,
            $handler->handle($request)
        );

        $this->assertTrue($ran);
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
                'uri' => '/test/Draft',
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
