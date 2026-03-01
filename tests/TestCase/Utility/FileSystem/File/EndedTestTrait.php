<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait EndedTestTrait
{
    public function testEnded(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w+');
        $file->write('test');

        $this->assertFalse(
            $file->ended()
        );

        $file->read(1);

        $this->assertTrue(
            $file->ended()
        );
    }

    public function testEndedEmpty(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('r');
        $file->read(1);

        $this->assertTrue(
            $file->ended()
        );
    }

    public function testEndedNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->ended();
    }
}
