<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\Session\Handlers\FileSessionHandler;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\TestCase;

use function clearstatcache;
use function fclose;
use function filemtime;
use function flock;
use function fopen;
use function glob;
use function mkdir;
use function rmdir;
use function session_id;
use function session_start;
use function session_write_close;
use function time;
use function touch;

use const LOCK_EX;
use const LOCK_NB;
use const LOCK_UN;

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

        $this->assertSame(
            [],
            glob('sessions/*')
        );
    }

    public function testOperationLocksAreReleased(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->write($id, 'data1')
        );

        $this->assertSame(
            'data1',
            $this->handler->read($id)
        );

        $handle = fopen('sessions/'.$id, 'c+b');

        $this->assertIsResource($handle);

        $this->assertTrue(
            flock($handle, LOCK_EX | LOCK_NB)
        );

        $this->assertTrue(
            flock($handle, LOCK_UN)
        );

        $this->assertTrue(
            fclose($handle)
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

    public function testStrictModeRejectsUnknownSessionId(): void
    {
        $suppliedId = 'attacker-selected';
        session_id($suppliedId);

        $this->assertTrue(
            session_start(['use_cookies' => false])
        );

        $generatedId = (string) session_id();

        $this->assertNotSame($suppliedId, $generatedId);

        $this->assertTrue(
            session_write_close()
        );

        $this->assertTrue(
            $this->handler->destroy($generatedId)
        );

        session_id('cli');
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

    public function testUpdateTimestamp(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $oldTime = time() - 10;

        $this->assertTrue(
            touch('sessions/'.$id, $oldTime)
        );

        $this->assertTrue(
            $this->handler->updateTimestamp($id, 'ignored')
        );

        clearstatcache(true, 'sessions/'.$id);

        $this->assertGreaterThan(
            $oldTime,
            filemtime('sessions/'.$id)
        );

        $this->assertSame(
            'data',
            $this->handler->read($id)
        );

        $this->assertFalse(
            $this->handler->updateTimestamp('missing', 'ignored')
        );
    }

    public function testValidateId(): void
    {
        $id = $this->session->id();

        $this->assertFalse(
            $this->handler->validateId($id)
        );

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertTrue(
            $this->handler->validateId($id)
        );

        $this->assertTrue(
            touch('sessions/'.$id, time() - 86400)
        );

        clearstatcache(true, 'sessions/'.$id);

        $this->assertFalse(
            $this->handler->validateId($id)
        );

        $this->assertFalse(
            $this->handler->validateId('../outside')
        );

        $this->assertFalse(
            $this->handler->write("valid\n", 'data')
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
        $handler = $this->session->getHandler();

        $this->assertInstanceOf(FileSessionHandler::class, $handler);

        $this->handler = $handler;

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
