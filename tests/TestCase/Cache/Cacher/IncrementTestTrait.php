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
            $this->cacher->increment('test')
        );
    }

    public function testIncrementAmount(): void
    {
        $this->assertSame(
            5,
            $this->cacher->increment('test', 5)
        );
    }

    public function testIncrementExisting(): void
    {
        $this->cacher->set('test', 5);
        $this->cacher->increment('test');

        $this->assertSame(
            6,
            $this->cacher->get('test')
        );
    }

    public function testIncrementInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->increment('test/');
    }

    public function testIncrementInvalidType(): void
    {
        $this->cacher->set('test', 'abc');

        $this->assertFalse(
            $this->cacher->decrement('test')
        );
    }

    public function testIncrementPersists(): void
    {
        $this->cacher->increment('test');

        $this->assertSame(
            1,
            $this->cacher->get('test')
        );
    }
}
