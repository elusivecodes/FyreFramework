<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\Session\Handlers\FileSessionHandler;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\TestCase;

use function glob;
use function mkdir;
use function rmdir;

final class FileTest extends TestCase
{
    protected FileSessionHandler $handler;

    protected Session $session;

    public function testGc(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data1')
        );

        $this->assertSame(
            1,
            $this->handler->gc(-10)
        );

        $this->assertEmpty(
            glob('sessions/*')
        );
    }

    public function testRead(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertSame(
            'data',
            $this->handler->read($id)
        );
    }

    public function testUpdate(): void
    {
        $id = $this->session->id();

        $this->assertSame(
            '',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data1')
        );

        $this->assertSame(
            'data1',
            $this->handler->read($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data2')
        );

        $this->assertSame(
            'data2',
            $this->handler->read($id)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(Session::class);
        $container->use(Config::class)->set('Session', [
            'handler' => [
                'className' => FileSessionHandler::class,
            ],
        ]);

        $this->session = $container->use(Session::class);
        $this->handler = $this->session->getHandler();

        $this->session->start();

        @mkdir('sessions');

        $this->assertTrue(
            $this->handler->open('sessions', '')
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->destroy($id)
        );

        $this->assertTrue(
            $this->handler->close()
        );

        @rmdir('sessions');
    }
}
