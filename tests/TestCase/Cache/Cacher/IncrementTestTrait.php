<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\InvalidArgumentException;

trait IncrementTestTrait
{
    public function testIncrement(): void
    {
        $this->assertSame(
            1,
            $this->cache->increment('test')
        );
    }

    public function testIncrementAmount(): void
    {
        $this->assertSame(
            5,
            $this->cache->increment('test', 5)
        );
    }

    public function testIncrementExisting(): void
    {
        $this->cache->set('test', 5);
        $this->cache->increment('test');

        $this->assertSame(
            6,
            $this->cache->get('test')
        );
    }

    public function testIncrementInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cache->increment('test/');
    }

    public function testIncrementInvalidType(): void
    {
        $this->cache->set('test', 'abc');

        $this->assertFalse(
            $this->cache->decrement('test')
        );
    }

    public function testIncrementPersists(): void
    {
        $this->cache->increment('test');

        $this->assertSame(
            1,
            $this->cache->get('test')
        );
    }
}
