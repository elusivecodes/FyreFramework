<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\ErrorHandler;
use Fyre\Core\Exceptions\ErrorException;
use Fyre\Core\Middleware\ErrorHandlerMiddleware;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\EventManager;
use Fyre\Http\ClientResponse;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ResponseEmitter;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Mock\Http\Middleware\ExceptionMiddleware;

use function class_uses;
use function trigger_error;

use const E_USER_WARNING;

final class ErrorHandlerMiddlewareTest extends TestCase
{
    protected Container $container;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ErrorHandlerMiddleware::class)
        );
    }

    public function testException(): void
    {
        $queue = new MiddlewareQueue();
        $queue->add(ErrorHandlerMiddleware::class);
        $queue->add(ExceptionMiddleware::class);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            500,
            $response->getStatusCode()
        );

        $this->assertSame(
            'Internal Server Error',
            $response->getBody()->getContents()
        );
    }

    public function testPhpError(): void
    {
        $errorHandler = $this->container->use(ErrorHandler::class);
        $errorHandler->register();

        $queue = new MiddlewareQueue();
        $queue->add(ErrorHandlerMiddleware::class);
        $queue->add(static function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            trigger_error('Sensitive warning', E_USER_WARNING);

            return new ClientResponse(['body' => 'Execution continued.']);
        });

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            500,
            $response->getStatusCode()
        );

        $this->assertSame(
            'Internal Server Error',
            $response->getBody()->getContents()
        );

        $this->assertInstanceOf(
            ErrorException::class,
            $errorHandler->getException()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(EventManager::class);
        $this->container->singleton(ErrorHandler::class);
        $this->container->singleton(ResponseEmitter::class);

        $this->container->use(Config::class)->set('Error', [
            'log' => false,
        ]);

        $this->container->use(ErrorHandler::class)->disableCli();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->container->use(ErrorHandler::class)->unregister();
    }
}
