<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

trait SizeTestTrait
{
    public function testSize(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');
        $file->close();

        $this->assertSame(
            4,
            $file->size()
        );
    }

    public function testSizeNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('filesize(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->size();
    }
}
