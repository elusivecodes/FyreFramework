<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @property Cacher $cacher
 */
trait GetSetTestTrait
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function setGetProvider(): array
    {
        return [
            'array' => [['key' => 'value']],
            'false' => [false],
            'true' => [true],
            'float' => [.5],
            'integer' => [5],
            'string' => ['value'],
        ];
    }

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
        $this->expectExceptionMessageIs('Cache key `test/` is not valid.');

        $this->cacher->get('test/');
    }

    #[DataProvider('setGetProvider')]
    public function testSetGet(mixed $value): void
    {
        $this->cacher->set('test', $value);

        $this->assertSame(
            $value,
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

        $cachedValues = $this->cacher->getMultiple(['test1', 'test2']);

        $this->assertIsArray($cachedValues);
        $this->assertArraysAreIdentical(
            [
                'test1' => 'value1',
                'test2' => 'value2',
            ],
            $cachedValues
        );
    }

    public function testSetGetMultipleZeroExpiry(): void
    {
        $values = [
            'test1' => 'value1',
            'test2' => 'value2',
        ];

        $this->cacher->setMultiple($values);

        $this->assertTrue(
            $this->cacher->setMultiple($values, 0)
        );

        $cachedValues = $this->cacher->getMultiple(['test1', 'test2']);

        $this->assertIsArray($cachedValues);
        $this->assertArraysAreIdentical(
            [
                'test1' => null,
                'test2' => null,
            ],
            $cachedValues
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

    public function testSetInvalidKey(): void
    {
        $this->expectException(CacheException::class);
        $this->expectExceptionMessageIs('Cache key `test/` is not valid.');

        $this->cacher->set('test/', 'value', 1);
    }

    public function testSetNegativeExpiry(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertTrue(
            $this->cacher->set('test', 'new', -1)
        );

        $this->assertNull(
            $this->cacher->get('test')
        );
    }

    public function testSetZeroExpiry(): void
    {
        $this->cacher->set('test', 'value');

        $this->assertTrue(
            $this->cacher->set('test', 'new', 0)
        );

        $this->assertNull(
            $this->cacher->get('test')
        );
    }
}
