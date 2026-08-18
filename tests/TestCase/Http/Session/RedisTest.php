<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\Session\Handlers\RedisSessionHandler;
use Fyre\Http\Session\Session;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function getenv;

#[RequiresPhpExtension('redis')]
final class RedisTest extends TestCase
{
    protected RedisSessionHandler $handler;

    protected Session $session;

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

    public function testUpdateTimestamp(): void
    {
        $id = $this->session->id();

        $this->assertTrue(
            $this->handler->write($id, 'data')
        );

        $this->assertTrue(
            $this->handler->updateTimestamp($id, 'ignored')
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

        $this->assertFalse(
            $this->handler->validateId('../outside')
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
                'className' => RedisSessionHandler::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
        ]);

        $this->session = $container->use(Session::class);
        $handler = $this->session->getHandler();

        $this->assertInstanceOf(RedisSessionHandler::class, $handler);

        $this->handler = $handler;

        $this->session->start();

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
    }
}
