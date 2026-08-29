<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Container;
use Fyre\Core\Engine;
use Fyre\Core\Loader;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ResponseEmitter;
use Fyre\Http\ServerRequest;
use Fyre\Router\RouteHandler;
use Fyre\Router\Router;
use Override;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

final class GettingStartedTest extends TestCase
{
    public function testHelloWorldApplication(): void
    {
        $previousContainer = Container::getInstance();
        $app = new class (new Loader()) extends Engine
        {
            #[Override]
            public function middleware(MiddlewareQueue $queue): MiddlewareQueue
            {
                return $queue
                    ->add('error')
                    ->add('router')
                    ->add('bindings');
            }
        };

        Engine::setInstance($app);
        $app->instance(
            ResponseEmitter::class,
            $this->getStubBuilder(ResponseEmitter::class)
                ->onlyMethods(['setHeader', 'setCookie'])
                ->getStub()
        );

        try {
            $router = $app->use(Router::class);
            $router->clear();
            $router->get(
                '/',
                static fn(): string => 'Hello, world!'
            );

            $request = $app->use(ServerRequest::class, [
                'options' => [
                    'method' => 'GET',
                    'server' => [
                        'REQUEST_URI' => '/',
                    ],
                ],
            ]);
            $handler = $app->use(RequestHandler::class, [
                'fallbackHandler' => $app->use(RouteHandler::class),
            ]);
            $response = $handler->handle($request);

            ob_start();
            $app->use(ResponseEmitter::class)->emit($response, $request);
            $output = ob_get_clean();

            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame('Hello, world!', $output);
        } finally {
            Container::setInstance($previousContainer);
        }
    }
}
