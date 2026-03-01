<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait WriteTestTrait
{
    public function testWrite(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');

        $this->assertSame(
            $file,
            $file->write('test')
        );

        $file->close();

        $this->assertSame(
            'test',
            $file->contents()
        );
    }

    public function testWriteAppends(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');
        $file->write('1');
        $file->close();

        $this->assertSame(
            'test1',
            $file->contents()
        );
    }

    public function testWriteInvalidHandle(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fwrite(): Write of 4 bytes failed with errno=9 Bad file descriptor');

        $file = new File('tmp/test.txt', true);
        $file->open('r');
        $file->write('test');
    }

    public function testWriteNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->write('test');
    }
}
