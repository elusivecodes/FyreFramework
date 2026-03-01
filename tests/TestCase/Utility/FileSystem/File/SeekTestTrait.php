<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait SeekTestTrait
{
    public function testSeek(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');

        $this->assertSame(
            $file,
            $file->seek(2)
        );

        $this->assertSame(
            2,
            $file->tell()
        );
    }

    public function testSeekNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->seek(0);
    }
}
