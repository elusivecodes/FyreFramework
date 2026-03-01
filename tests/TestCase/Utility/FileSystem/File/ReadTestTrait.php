<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait ReadTestTrait
{
    public function testRead(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w+');
        $file->write('test');
        $file->rewind();

        $this->assertSame(
            'te',
            $file->read(2)
        );
    }

    public function testReadInvalidHandle(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fread(): Read of 8192 bytes failed with errno=9 Bad file descriptor');

        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->read(4);
    }

    public function testReadNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->read(4);
    }
}
