<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Exceptions\CacheException;

use function sleep;

trait GetSetTestTrait
{
    public function testGetDefault(): void
    {
        $this->assertSame(
            123,
            $this->cache->get('test', 123)
        );
    }

    public function testGetInvalidKey(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cache->get('test/');
    }

    public function testSetExpiry(): void
    {
        $this->cache->set('test', 'value', 1);

        sleep(2);

        $this->assertNull(
            $this->cache->get('test')
        );
    }

    public function testSetGetArray(): void
    {
        $this->cache->set('test', ['key' => 'value']);

        $this->assertSame(
            ['key' => 'value'],
            $this->cache->get('test')
        );
    }

    public function testSetGetBooleanFalse(): void
    {
        $this->cache->set('test', false);

        $this->assertFalse(
            $this->cache->get('test')
        );
    }

    public function testSetGetBooleanTrue(): void
    {
        $this->cache->set('test', true);

        $this->assertTrue(
            $this->cache->get('test')
        );
    }

    public function testSetGetFloat(): void
    {
        $this->cache->set('test', .5);

        $this->assertSame(
            .5,
            $this->cache->get('test')
        );
    }

    public function testSetGetInteger(): void
    {
        $this->cache->set('test', 5);

        $this->assertSame(
            5,
            $this->cache->get('test')
        );
    }

    public function testSetGetMultiple(): void
    {
        $this->assertTrue(
            $this->cache->setMultiple([
                'test1' => 'value1',
                'test2' => 'value2',
            ])
        );

        $this->assertSame(
            [
                'test1' => 'value1',
                'test2' => 'value2',
            ],
            $this->cache->getMultiple(['test1', 'test2'])
        );
    }

    public function testSetGetObject(): void
    {
        $object = (object) ['key' => 'value'];

        $this->cache->set('test', $object);

        $this->assertEquals(
            $object,
            $this->cache->get('test')
        );
    }

    public function testSetGetString(): void
    {
        $this->cache->set('test', 'value');

        $this->assertSame(
            'value',
            $this->cache->get('test')
        );
    }

    public function testSetInvalidKey(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cache->set('test/', 'value', 1);
    }
}
