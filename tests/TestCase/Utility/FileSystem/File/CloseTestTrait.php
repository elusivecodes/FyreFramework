<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait CloseTestTrait
{
    public function testClose(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->close();
        $file->write('test');
    }

    public function testCloseNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->close();
    }
}
