<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Closure;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Session\Handlers\FileSessionHandler;
use Fyre\Http\Session\Session;
use Fyre\Http\Session\SessionHandler;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function time;

final class SessionTest extends TestCase
{
    protected Session $session;

    public function testClear(): void
    {
        $this->session->set('test', 'value');
        $this->session->clear();

        $this->assertFalse(
            $this->session->has('test')
        );
    }

    public function testConsume(): void
    {
        $this->session->set('test', 'value');

        $this->assertSame(
            'value',
            $this->session->consume('test')
        );

        $this->assertFalse(
            $this->session->has('test')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Session::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(SessionHandler::class)
        );
    }

    public function testDestroy(): void
    {
        $this->session->set('test', 'value');
        $this->session->destroy();

        $this->assertFalse(
            $this->session->isStarted()
        );

        $this->assertNull(
            $this->session->get('test')
        );
    }

    public function testGet(): void
    {
        $this->session->set('test', 'value');

        $this->assertSame(
            'value',
            $this->session->get('test')
        );
    }

    public function testHas(): void
    {
        $this->session->set('test', 'value');

        $this->assertTrue(
            $this->session->has('test')
        );
    }

    public function testId(): void
    {
        $this->assertSame(
            'cli',
            $this->session->id()
        );
    }

    public function testInvalidCookieExpiration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Session cookie option `expires` must not be negative.');

        $config = new Config();
        $config->set('Session', [
            'cookie' => [
                'expires' => -1,
            ],
        ]);

        new Session(new Container(), $config);
    }

    public function testInvalidExpiration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Session option `expires` must be greater than 0.');

        $config = new Config();
        $config->set('Session', [
            'expires' => 0,
        ]);

        new Session(new Container(), $config);
    }

    public function testInvalidHandlerExpiration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Session handler option `expires` must be greater than 0.');

        new FileSessionHandler($this->session, [
            'expires' => 0,
        ]);
    }

    public function testIsActive(): void
    {
        $this->assertFalse(
            $this->session->isActive()
        );
    }

    public function testIsStarted(): void
    {
        $this->assertTrue(
            $this->session->isStarted()
        );

        $this->session->close();

        $this->assertFalse(
            $this->session->isStarted()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Session::class)
        );
    }

    public function testSetFlash(): void
    {
        $this->session->setFlash('test', 'value');

        $this->assertTrue(
            $this->session->has('test')
        );

        Closure::bind(function(): void {
            /** @var Session $this */
            $this->rotateFlashData();
            $this->clearTempData();
        }, $this->session, Session::class)();

        $this->assertFalse(
            $this->session->has('test')
        );
    }

    public function testSetTemp(): void
    {
        $this->session->setTemp('test', 'value', 2);

        $this->assertTrue(
            $this->session->has('test')
        );

        Closure::bind(function(): void {
            /** @var Session $this */
            $this->clearTempData();
        }, $this->session, Session::class)();

        $this->assertTrue(
            $this->session->has('test')
        );

        $_SESSION['_temp']['test'] = time() - 1;

        Closure::bind(function(): void {
            /** @var Session $this */
            $this->clearTempData();
        }, $this->session, Session::class)();

        $this->assertFalse(
            $this->session->has('test')
        );
    }

    public function testStartReadOnly(): void
    {
        $this->session->close();

        $this->assertFalse(
            $this->session->isStarted()
        );

        $this->session->startReadOnly();

        $this->assertFalse(
            $this->session->isStarted()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(Session::class);

        $this->session = $container->use(Session::class);

        $this->session->start();
    }
}
