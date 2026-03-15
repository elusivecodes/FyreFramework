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
            $this->cacher->get('test', 123)
        );
    }

    public function testGetInvalidKey(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->get('test/');
    }

    public function testSetExpiry(): void
    {
        $this->cacher->set('test', 'value', 1);

        sleep(2);

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testSetGetArray(): void
    {
        $this->cacher->set('test', ['key' => 'value']);

        $this->assertSame(
            ['key' => 'value'],
            $this->cacher->get('test')
        );
    }

    public function testSetGetBooleanFalse(): void
    {
        $this->cacher->set('test', false);

        $this->assertFalse(
            $this->cacher->get('test')
        );
    }

    public function testSetGetBooleanTrue(): void
    {
        $this->cacher->set('test', true);

        $this->assertTrue(
            $this->cacher->get('test')
        );
    }

    public function testSetGetFloat(): void
    {
        $this->cacher->set('test', .5);

        $this->assertSame(
            .5,
            $this->cacher->get('test')
        );
    }

    public function testSetGetInteger(): void
    {
        $this->cacher->set('test', 5);

        $this->assertSame(
            5,
            $this->cacher->get('test')
        );
    }

    public function testSetGetMultiple(): void
    {
        $this->assertTrue(
            $this->cacher->setMultiple([
                'test1' => 'value1',
                'test2' => 'value2',
            ])
        );

        $this->assertSame(
            [
                'test1' => 'value1',
                'test2' => 'value2',
            ],
            $this->cacher->getMultiple(['test1', 'test2'])
        );
    }

    public function testSetGetObject(): void
    {
        $object = (object) ['key' => 'value'];

        $this->cacher->set('test', $object);

        $this->assertEquals(
            $object,
            $this->cacher->get('test')
        );
    }

    public function testSetGetString(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertSame(
            'value',
            $this->cacher->get('test')
        );
    }

    public function testSetInvalidKey(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('Cache key `test/` is not valid.');

        $this->cacher->set('test/', 'value', 1);
    }
}
