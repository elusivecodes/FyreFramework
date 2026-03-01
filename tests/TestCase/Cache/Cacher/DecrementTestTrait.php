<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\InvalidArgumentException;

trait DecrementTestTrait
{
    public function testDecrement(): void
    {
        $this->cache->set('test', 5);

        $this->assertSame(
            4,
            $this->cache->decrement('test')
        );
    }

    public function testDecrementAmount(): void
    {
        $this->cache->set('test', 10);

        $this->assertSame(
            5,
            $this->cache->decrement('test', 5)
        );
    }

    public function testDecrementInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cache->decrement('test/');
    }

    public function testDecrementInvalidType(): void
    {
        $this->cache->set('test', 'abc');

        $this->assertFalse(
            $this->cache->decrement('test')
        );
    }

    public function testDecrementPersists(): void
    {
        $this->cache->set('test', 5);
        $this->cache->decrement('test');

        $this->assertSame(
            4,
            $this->cache->get('test')
        );
    }
}
