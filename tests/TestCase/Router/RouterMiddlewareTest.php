<?php
declare(strict_types=1);

namespace Tests\TestCase\Router;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
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
use Tests\Mock\Controllers\TestController;
use Tests\Mock\Enums\ReviewStatus;
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
    }

    public function testProcessClosureRoute(): void
    {
        $destination = static fn(): string => 'This is a test response';

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

        $response = $handler->handle($request);

        $this->assertSame(
            'This is a test response',
            $response->getBody()->getContents()
        );
    }

    public function testProcessControllerRoute(): void
    {
        $this->router->connect('test', TestController::class);

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

        $this->assertSame(
            'This is a test response',
            $response->getBody()->getContents()
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

        $this->assertSame(
            302,
            $response->getStatusCode()
        );
    }

    public function testProcessRedirectRouteLocation(): void
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

        $this->assertSame(
            'https://test.com/',
            $response->getHeaderLine('Location')
        );
    }

    public function testProcessRouteBackedEnumParam(): void
    {
        $arguments = [];
        $destination = static function(Status $status) use (&$arguments): string {
            $arguments = [$status];

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

        $handler->handle($request);

        $this->assertSame(
            [Status::Draft],
            $arguments
        );
    }

    public function testProcessRouteBackedEnumParamEncoded(): void
    {
        $arguments = [];
        $destination = static function(ReviewStatus $status) use (&$arguments): string {
            $arguments = [$status];

            return '';
        };

        $this->router->connect('test/{status}', $destination, as: 'status');

        $url = $this->router->url('status', [
            'status' => ReviewStatus::Pending,
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
                'uri' => $url,
            ],
        ]);

        $handler->handle($request);

        $this->assertSame(
            [ReviewStatus::Pending],
            $arguments
        );
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

    public function testProcessRouteBindingCallbackRequest(): void
    {
        $previousValue = null;
        $destination = static fn(int $a, int $b): string => '';

        $this->router->connect('test/{a}/{b}', $destination, bindingCallbacks: [
            'a' => static fn(string $value): int => (int) $value,
            'b' => static function(string $value, ServerRequestInterface $request) use (&$previousValue): int {
                $previousValue = $request->getAttribute('routeArguments')['a'];

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

        $handler->handle($request);

        $this->assertSame(1, $previousValue);
    }

    public function testProcessRouteBindingCallbacks(): void
    {
        $arguments = [];
        $destination = static function(int $a, int $b) use (&$arguments): string {
            $arguments = [$a, $b];

            return '';
        };

        $this->router->connect('test/{a}/{b}', $destination, bindingCallbacks: [
            'a' => static fn(string $value): int => (int) $value,
            'b' => static fn(string $value): int => (int) $value,
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

        $handler->handle($request);

        $this->assertSame(
            [1, 2],
            $arguments
        );
    }

    public function testProcessRouteParamDefault(): void
    {
        $arguments = [];
        $destination = static function(string $value = 'default') use (&$arguments): string {
            $arguments = [$value];

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

        $this->assertSame(
            ['default'],
            $arguments
        );
    }

    public function testProcessRouteParamDefaultSkipsBindingCallback(): void
    {
        $values = [];
        $destination = static fn(string $value = 'default'): string => '';

        $this->router->connect('test/{value?}', $destination, bindingCallbacks: [
            'value' => static function(string $value) use (&$values): string {
                $values[] = $value;

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

        $handler->handle($request);

        $this->assertSame([], $values);
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
        $arguments = [];
        $destination = static function(string|null $value) use (&$arguments): string {
            $arguments = [$value];

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

        $this->assertSame(
            [null],
            $arguments
        );
    }

    public function testProcessRouteUnitEnumParam(): void
    {
        $arguments = [];
        $destination = static function(State $state) use (&$arguments): string {
            $arguments = [$state];

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

        $handler->handle($request);

        $this->assertSame(
            [State::Draft],
            $arguments
        );
    }

    public function testSubstituteBindingsDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(SubstituteBindingsMiddleware::class)
        );
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
