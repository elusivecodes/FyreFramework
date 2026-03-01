<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Core\Config;
use Fyre\Core\Engine;
use Fyre\Core\Lang;
use Fyre\Core\Loader;
use Fyre\Event\Event;
use Fyre\Http\MiddlewareQueue;
use Fyre\Router\Router;
use Override;
use PHPUnit\Framework\TestCase;

final class EngineTest extends TestCase
{
    protected Engine $app;

    public function testBootstrap(): void
    {
        $this->assertSame(
            'Test',
            $this->app->use(Config::class)->get('App.value')
        );
    }

    public function testEventBuildMiddleware(): void
    {
        $ran = false;
        $this->app->getEventManager()->on('Engine.buildMiddleware', static function(Event $event, MiddlewareQueue $middlewareQueue) use (&$ran): void {
            $ran = true;
        });

        $this->app->use(MiddlewareQueue::class);

        $this->assertTrue($ran);
    }

    public function testLang(): void
    {
        $this->assertSame(
            'Test',
            $this->app->use(Lang::class)->get('Values.test')
        );
    }

    public function testRoutes(): void
    {
        $this->assertSame(
            'https://test.com/',
            $this->app->use(Router::class)->getBaseUri()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $loader = new Loader();
        $this->app = new Engine($loader);

        Engine::setInstance($this->app);

        $this->app->use(Config::class)
            ->load('functions')
            ->load('app');
    }
}
