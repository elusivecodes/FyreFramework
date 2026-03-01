<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait LockTestTrait
{
    public function testLock(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('r');

        $this->assertSame(
            $file,
            $file->lock()
        );
    }

    public function testLockNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt');
        $file->lock();
    }

    public function testUnlock(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('r');

        $this->assertSame(
            $file,
            $file->unlock()
        );
    }

    public function testUnlockNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt');
        $file->unlock();
    }
}
