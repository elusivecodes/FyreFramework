<?php
declare(strict_types=1);

namespace Tests\TestCase\Cache\Cacher;

use Fyre\Cache\Cacher;
use Fyre\Cache\Exceptions\CacheException;
use Fyre\Cache\Exceptions\InvalidArgumentException;
use RuntimeException;

use function sleep;

/**
 * @property Cacher $cacher
 */
trait LockTestTrait
{
    public function testLock(): void
    {
        $lock = $this->cacher->lock('test');

        $this->assertFalse(
            $lock->isAcquired()
        );

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $lock->isAcquired()
        );

        $this->assertTrue(
            $lock->refresh()
        );

        $this->assertTrue(
            $lock->release()
        );

        $this->assertFalse(
            $lock->isAcquired()
        );
    }

    public function testLockAlreadyAcquired(): void
    {
        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $lock->release()
        );
    }

    public function testLockContention(): void
    {
        $firstLock = $this->cacher->lock('test');
        $secondLock = $this->cacher->lock('test');

        $this->assertTrue(
            $firstLock->acquire()
        );

        $this->assertFalse(
            $secondLock->acquire()
        );

        $this->assertTrue(
            $firstLock->release()
        );

        $this->assertTrue(
            $secondLock->acquire()
        );

        $this->assertTrue(
            $secondLock->release()
        );
    }

    public function testLockDoesNotReleaseNewOwner(): void
    {
        $firstLock = $this->cacher->lock('test', 1);

        $this->assertTrue(
            $firstLock->acquire()
        );

        sleep(2);

        $secondLock = $this->cacher->lock('test');

        $this->assertTrue(
            $secondLock->acquire()
        );

        $this->assertFalse(
            $firstLock->release()
        );

        $thirdLock = $this->cacher->lock('test');

        $this->assertFalse(
            $thirdLock->acquire()
        );

        $secondLock->release();
    }

    public function testLockInvalidExpiration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache lock expiration must be greater than 0.');

        $this->cacher->lock('test', 0);
    }

    public function testLockInvalidKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key `__lock__.test/` is not valid.');

        $this->cacher->lock('test/');
    }

    public function testLockInvalidWait(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache lock wait time must not be negative.');

        $this->cacher->lock('test')->acquire(-1);
    }

    public function testLockUnavailable(): void
    {
        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        try {
            $this->cacher->synchronized('test', static fn(): null => null);
            $this->fail('Expected cache lock contention to throw.');
        } catch (CacheException $e) {
            $this->assertSame(
                'Cache lock `test` could not be acquired.',
                $e->getMessage()
            );
        } finally {
            $lock->release();
        }
    }

    public function testSynchronized(): void
    {
        $calls = 0;

        $result = $this->cacher->synchronized(
            'test',
            static function() use (&$calls): string {
                $calls++;

                return 'result';
            }
        );

        $this->assertSame(
            'result',
            $result
        );

        $this->assertSame(
            1,
            $calls
        );

        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $lock->release();
    }

    public function testSynchronizedReleasesLockAfterException(): void
    {
        try {
            $this->cacher->synchronized(
                'test',
                static fn(): never => throw new RuntimeException('Test exception.')
            );
        } catch (RuntimeException $e) {
            $this->assertSame(
                'Test exception.',
                $e->getMessage()
            );
        }

        $lock = $this->cacher->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $lock->release();
    }
}
