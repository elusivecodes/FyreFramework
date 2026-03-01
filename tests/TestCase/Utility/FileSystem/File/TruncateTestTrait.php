<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait TruncateTestTrait
{
    public function testTruncate(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');

        $this->assertSame(
            $file,
            $file->truncate()
        );

        $file->close();

        $this->assertSame(
            '',
            $file->contents()
        );
    }

    public function testTruncateInvalidHandle(): void
    {
        $this->expectException(RuntimeException::class);

        $file = new File('tmp/test.txt', true);
        $file->open('r');
        $file->truncate();
    }

    public function testTruncateNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->close();
        $file->truncate();
    }

    public function testTruncateSize(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->write('test');
        $file->truncate(2);
        $file->close();

        $this->assertSame(
            'te',
            $file->contents()
        );
    }
}
