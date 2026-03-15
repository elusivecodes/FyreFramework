<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

trait EmptyTestTrait
{
    public function testEmpty(): void
    {
        $this->cacher->set('test1', 'value');
        $this->cacher->set('test2', 'value');

        $this->assertTrue(
            $this->cacher->clear()
        );

        $this->assertFalse(
            $this->cacher->has('test')
        );

        $this->assertFalse(
            $this->cacher->has('test2')
        );
    }
}
