<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\InvalidArgumentException;

trait DeleteTestTrait
{
    public function testDelete(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertTrue(
            $this->cacher->delete('test')
        );

        $this->assertFalse(
            $this->cacher->has('test')
        );
    }

    public function testDeleteInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->delete('test/');
    }

    public function testDeleteMissing(): void
    {
        $this->assertFalse(
            $this->cacher->delete('missing')
        );
    }

    public function testDeleteMultiple(): void
    {
        $this->cacher->set('test1', 'value1');
        $this->cacher->set('test2', 'value2');

        $this->assertTrue(
            $this->cacher->deleteMultiple(['test1', 'test2'])
        );

        $this->assertFalse(
            $this->cacher->has('test1')
        );

        $this->assertFalse(
            $this->cacher->has('test2')
        );
    }
}
