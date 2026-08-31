<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Forge\Presets\LocksPreset;
use Fyre\DB\Lock;
use InvalidArgumentException;

use function class_uses;
use function sleep;
use function str_repeat;

/**
 * @property Connection $db
 */
trait LockTestTrait
{
    public function testLockAcquire(): void
    {
        $lock = $this->db->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $lock->release()
        );
    }

    public function testLockAcquireDifferentNames(): void
    {
        $lock = $this->db->lock('test');
        $otherLock = $this->db->lock('other');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $otherLock->acquire()
        );

        $this->assertTrue(
            $lock->release()
        );

        $this->assertTrue(
            $otherLock->release()
        );
    }

    public function testLockAcquireExpired(): void
    {
        $lock = $this->db->lock('test', 1);

        $this->assertTrue(
            $lock->acquire()
        );

        sleep(2);

        $otherLock = $this->db->lock('test');

        $this->assertTrue(
            $otherLock->acquire()
        );

        $this->assertFalse(
            $lock->refresh()
        );

        $this->assertFalse(
            $lock->release()
        );

        $this->assertTrue(
            $otherLock->release()
        );
    }

    public function testLockAcquireLocked(): void
    {
        $lock = $this->db->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $otherLock = $this->db->lock('test');

        $this->assertFalse(
            $otherLock->acquire()
        );

        $this->assertTrue(
            $lock->release()
        );
    }

    public function testLockDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Lock::class)
        );
    }

    public function testLockExpirationInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Lock expiration must be greater than 0.');

        $this->db->lock('test', 0);
    }

    public function testLockNameInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Lock name must not be empty.');

        $this->db->lock('');
    }

    public function testLockNameTooLong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Lock name must not exceed 255 characters.');

        $this->db->lock(str_repeat('a', LocksPreset::NAME_LENGTH + 1));
    }

    public function testLockRefresh(): void
    {
        $lock = $this->db->lock('test');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->assertTrue(
            $lock->refresh()
        );

        $this->assertTrue(
            $lock->release()
        );
    }

    public function testLockWaitInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Lock wait time must not be negative.');

        $lock = $this->db->lock('test');
        $lock->acquire(-1);
    }
}
