<?php
declare(strict_types=1);

namespace Tests\TestCase\Security;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Security\Middleware\CspMiddleware;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class CspMiddlewareTest extends TestCase
{
    protected Config $config;

    protected Container $container;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CspMiddleware::class)
        );
    }

    public function testPolicy(): void
    {
        $this->config->set('Csp', [
            'default' => [
                'default-src' => 'self',
            ],
        ]);
        $middleware = $this->container->build(CspMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            'default-src \'self\';',
            $response->getHeaderLine('Content-Security-Policy')
        );

        $this->assertSame(
            '',
            $response->getHeaderLine('Content-Security-Policy-Report-Only')
        );
    }

    public function testReportPolicy(): void
    {
        $this->config->set('Csp', [
            'report' => [
                'default-src' => 'self',
                'report-to' => 'csp-endpoint',
            ],
            'reportTo' => [
                'group' => 'csp-endpoint',
                'max_age' => '10886400',
                'endpoints' => [
                    [
                        'url' => 'https://test.com/csp-report',
                    ],
                ],
            ],
        ]);
        $middleware = $this->container->build(CspMiddleware::class);

        $queue = new MiddlewareQueue();
        $queue->add($middleware);

        $handler = $this->container->build(RequestHandler::class, ['queue' => $queue]);
        $request = $this->container->build(ServerRequest::class);

        $response = $handler->handle($request);

        $this->assertSame(
            'default-src \'self\'; report-to csp-endpoint;',
            $response->getHeaderLine('Content-Security-Policy-Report-Only')
        );

        $this->assertSame(
            '{"group":"csp-endpoint","max_age":"10886400","endpoints":[{"url":"https://test.com/csp-report"}]}',
            $response->getHeaderLine('Report-To')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);

        $this->config = $this->container->use(Config::class);
    }
}
