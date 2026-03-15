<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\InvalidArgumentException;

use function sleep;

trait RememberTestTrait
{
    public function testRemember(): void
    {
        $this->cacher->set('test', 1);

        $this->assertSame(
            1,
            $this->cacher->remember('test', static fn() => 2)
        );
    }

    public function testRememberExpiry(): void
    {
        $this->cacher->remember('test', static fn() => 2, 1);

        sleep(2);

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testRememberInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->remember('test/', static fn() => 2);
    }

    public function testRememberMissing(): void
    {
        $this->assertSame(
            2,
            $this->cacher->remember('test', static fn() => 2)
        );
    }

    public function testRememberPersists(): void
    {
        $this->cacher->remember('test', static fn() => 2);

        $this->assertSame(
            2,
            $this->cacher->get('test')
        );
    }
}
