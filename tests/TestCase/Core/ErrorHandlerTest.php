<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Exception;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\ErrorHandler;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\BadRequestException;
use Fyre\Http\Exceptions\ConflictException;
use Fyre\Http\Exceptions\ForbiddenException;
use Fyre\Http\Exceptions\GoneException;
use Fyre\Http\Exceptions\InternalServerException;
use Fyre\Http\Exceptions\MethodNotAllowedException;
use Fyre\Http\Exceptions\NotAcceptableException;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\Exceptions\NotImplementedException;
use Fyre\Http\Exceptions\ServiceUnavailableException;
use Fyre\Http\Exceptions\UnauthorizedException;
use Fyre\Http\ResponseEmitter;
use Override;
use PHPUnit\Framework\TestCase;
use Throwable;

use function class_uses;

final class ErrorHandlerTest extends TestCase
{
    protected Container $container;

    protected ErrorHandler $errorHandler;

    public function testBadRequest(): void
    {
        $response = $this->errorHandler->render(new BadRequestException());

        $this->assertSame(
            400,
            $response->getStatusCode()
        );
    }

    public function testConflict(): void
    {
        $response = $this->errorHandler->render(new ConflictException());

        $this->assertSame(
            409,
            $response->getStatusCode()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ErrorHandler::class)
        );
    }

    public function testEventBeforeRender(): void
    {
        $ran = false;
        $this->errorHandler->getEventManager()->on('Error.beforeRender', function(Event $event, Throwable $exception) use (&$ran): void {
            $ran = true;

            $this->assertInstanceOf(ConflictException::class, $exception);
        });

        $this->errorHandler->render(new ConflictException());

        $this->assertTrue($ran);
    }

    public function testForbidden(): void
    {
        $response = $this->errorHandler->render(new ForbiddenException());

        $this->assertSame(
            403,
            $response->getStatusCode()
        );
    }

    public function testGone(): void
    {
        $response = $this->errorHandler->render(new GoneException());

        $this->assertSame(
            410,
            $response->getStatusCode()
        );
    }

    public function testHandle(): void
    {
        $exception = new Exception('Error');
        $response = $this->errorHandler->render($exception);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            500,
            $response->getStatusCode()
        );

        $this->assertSame(
            '<pre>'.$exception.'</pre>',
            $response->getBody()->getContents()
        );
    }

    public function testInternalServer(): void
    {
        $response = $this->errorHandler->render(new InternalServerException());

        $this->assertSame(
            500,
            $response->getStatusCode()
        );
    }

    public function testMethodNotAllowed(): void
    {
        $response = $this->errorHandler->render(new MethodNotAllowedException());

        $this->assertSame(
            405,
            $response->getStatusCode()
        );
    }

    public function testNotAcceptable(): void
    {
        $response = $this->errorHandler->render(new NotAcceptableException());

        $this->assertSame(
            406,
            $response->getStatusCode()
        );
    }

    public function testNotFound(): void
    {
        $response = $this->errorHandler->render(new NotFoundException());

        $this->assertSame(
            404,
            $response->getStatusCode()
        );
    }

    public function testNotImplemented(): void
    {
        $response = $this->errorHandler->render(new NotImplementedException());

        $this->assertSame(
            501,
            $response->getStatusCode()
        );
    }

    public function testRenderer(): void
    {
        $ran = false;
        $renderer = static function(Throwable $exception) use (&$ran): string {
            $ran = true;

            return $exception->getMessage();
        };

        $this->assertSame(
            $this->errorHandler,
            $this->errorHandler->setRenderer($renderer)
        );

        $this->assertSame(
            $renderer,
            $this->errorHandler->getRenderer()
        );

        $exception = new Exception('Error');
        $response = $this->errorHandler->render($exception);

        $this->assertTrue($ran);

        $this->assertSame(
            'Error',
            $response->getBody()->getContents()
        );
    }

    public function testServiceUnavailable(): void
    {
        $response = $this->errorHandler->render(new ServiceUnavailableException());

        $this->assertSame(
            503,
            $response->getStatusCode()
        );
    }

    public function testUnauthorized(): void
    {
        $response = $this->errorHandler->render(new UnauthorizedException());

        $this->assertSame(
            401,
            $response->getStatusCode()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(EventManager::class);
        $this->container->singleton(ResponseEmitter::class);
        $this->container->use(Config::class)->set('Error', [
            'log' => false,
        ]);

        $this->errorHandler = $this->container->use(ErrorHandler::class);
        $this->errorHandler->disableCli();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->errorHandler->unregister();
    }
}
