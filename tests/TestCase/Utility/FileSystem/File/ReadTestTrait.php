<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;
use InvalidArgumentException;
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
        $this->expectExceptionMessageIs('fread(): Read of 8192 bytes failed with errno=9 Bad file descriptor');

        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->read(4);
    }

    public function testReadInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Read length must be greater than 0.');

        new File('tmp/test.txt', true)->read(0);
    }

    public function testReadNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->read(4);
    }
}
