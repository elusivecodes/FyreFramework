<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

use function time;

trait AccessTimeTestTrait
{
    public function testAccessTime(): void
    {
        $time = time();

        $file = new File('tmp/test.txt', true);

        $file->touch($time, $time);

        $this->assertSame(
            $time,
            $file->accessTime()
        );
    }

    public function testAccessTimeNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fileatime(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->accessTime();
    }
}
