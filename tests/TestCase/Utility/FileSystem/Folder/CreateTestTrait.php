<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\Folder;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;
use Fyre\Utility\FileSystem\Folder;

trait CreateTestTrait
{
    public function testCreate(): void
    {
        $folder = new Folder('tmp/test');

        $this->assertSame(
            $folder,
            $folder->create()
        );

        $this->assertTrue(
            $folder->exists()
        );
    }

    public function testCreateExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('mkdir(): File exists');

        $folder = new Folder('tmp/test', true);
        $folder->create();
    }

    public function testCreateExistsFile(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('mkdir(): File exists');

        new File('tmp/test', true);
        $folder = new Folder('tmp/test', true);
        $folder->create();
    }
}
