<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

use function time;

trait ModifiedTimeTestTrait
{
    public function testModifiedTime(): void
    {
        $time = time();

        $file = new File('tmp/test.txt', true);

        $file->touch($time);

        $this->assertSame(
            $time,
            $file->modifiedTime()
        );
    }

    public function testModifiedTimeNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('filemtime(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->modifiedTime();
    }
}
