<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait RewindTestTrait
{
    public function testRewind(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');

        $this->assertSame(
            $file,
            $file->rewind()
        );

        $this->assertSame(
            0,
            $file->tell()
        );
    }

    public function testRewindNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->rewind();
    }
}
