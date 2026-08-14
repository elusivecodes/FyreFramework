<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\InvalidArgumentException;

/**
 * @property Cacher $cacher
 */
trait RememberTestTrait
{
    public function testRemember(): void
    {
        $this->cacher->set('test', 1);

        $this->assertSame(
            1,
            $this->cacher->remember('test', static fn(): int => 2)
        );
    }

    public function testRememberInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->remember('test/', static fn(): int => 2);
    }

    public function testRememberMissing(): void
    {
        $this->assertSame(
            2,
            $this->cacher->remember('test', static fn(): int => 2)
        );
    }

    public function testRememberPersists(): void
    {
        $this->cacher->remember('test', static fn(): int => 2);

        $this->assertSame(
            2,
            $this->cacher->get('test')
        );
    }

    public function testRememberZeroExpiry(): void
    {
        $this->assertSame(
            2,
            $this->cacher->remember('test', static fn(): int => 2, 0)
        );

        $this->assertNull(
            $this->cacher->get('test')
        );
    }
}
