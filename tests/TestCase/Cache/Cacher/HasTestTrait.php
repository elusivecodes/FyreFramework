<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\InvalidArgumentException;

trait HasTestTrait
{
    public function testHas(): void
    {
        $this->cache->set('test', 1);

        $this->assertTrue(
            $this->cache->has('test')
        );
    }

    public function testHasInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cache->has('test/');
    }

    public function testHasMissing(): void
    {
        $this->assertFalse(
            $this->cache->has('test')
        );
    }
}
