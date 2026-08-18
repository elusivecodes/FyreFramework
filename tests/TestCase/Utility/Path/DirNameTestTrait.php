<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait DirNameTestTrait
{
    public function testDirNameWithDeepPath(): void
    {
        $this->assertSame(
            'sub/dir',
            Path::dirName('sub/dir/file.ext')
        );
    }

    public function testDirNameWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Path::dirName('')
        );
    }

    public function testDirNameWithFileName(): void
    {
        $this->assertSame(
            '.',
            Path::dirName('file.ext')
        );
    }

    public function testDirNameWithFullPath(): void
    {
        $this->assertSame(
            '/sub/dir',
            Path::dirName('/sub/dir/file.ext')
        );
    }

    public function testDirNameWithMultipleExtensions(): void
    {
        $this->assertSame(
            'dir',
            Path::dirName('dir/file.tst.ext')
        );
    }

    public function testDirNameWithNoExtension(): void
    {
        $this->assertSame(
            'dir',
            Path::dirName('dir/file')
        );
    }

    public function testDirNameWithPath(): void
    {
        $this->assertSame(
            'dir',
            Path::dirName('dir/file.ext')
        );
    }
}
