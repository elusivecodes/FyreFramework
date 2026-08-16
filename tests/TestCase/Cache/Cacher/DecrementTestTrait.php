<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\InvalidArgumentException;

/**
 * @property Cacher $cacher
 */
trait DecrementTestTrait
{
    public function testDecrement(): void
    {
        $this->cacher->set('test', 5);

        $this->assertSame(
            4,
            $this->cacher->decrement('test')
        );
    }

    public function testDecrementAmount(): void
    {
        $this->cacher->set('test', 10);

        $this->assertSame(
            5,
            $this->cacher->decrement('test', 5)
        );
    }

    public function testDecrementInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cache key `test/` is not valid.');

        $this->cacher->decrement('test/');
    }

    public function testDecrementInvalidType(): void
    {
        $this->cacher->set('test', 'abc');

        $this->assertFalse(
            $this->cacher->decrement('test')
        );
    }

    public function testDecrementPersists(): void
    {
        $this->cacher->set('test', 5);
        $this->cacher->decrement('test');

        $this->assertSame(
            4,
            $this->cacher->get('test')
        );
    }
}
