<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait CreateTestTrait
{
    public function testCreate(): void
    {
        $file = new File('tmp/test.txt');

        $this->assertSame(
            $file,
            $file->create()
        );

        $this->assertTrue(
            $file->exists()
        );
    }

    public function testCreateExists(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File `tmp/test.txt` already exists.');

        $file = new File('tmp/test.txt', true);
        $file->create();
    }

    public function testCreateFolder(): void
    {
        $file = new File('tmp/test/test.txt');

        $file->create();

        $this->assertTrue(
            $file->exists()
        );
    }
}
