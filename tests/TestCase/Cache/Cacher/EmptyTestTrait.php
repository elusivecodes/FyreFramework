<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

trait EmptyTestTrait
{
    public function testEmpty(): void
    {
        $this->cache->set('test1', 'value');
        $this->cache->set('test2', 'value');

        $this->assertTrue(
            $this->cache->clear()
        );

        $this->assertFalse(
            $this->cache->has('test')
        );

        $this->assertFalse(
            $this->cache->has('test2')
        );
    }
}
