<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait ExtensionTestTrait
{
    public function testExtensionWithDeepPath(): void
    {
        $this->assertSame(
            'ext',
            Path::extension('sub/dir/file.ext')
        );
    }

    public function testExtensionWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Path::extension('')
        );
    }

    public function testExtensionWithFileName(): void
    {
        $this->assertSame(
            'ext',
            Path::extension('file.ext')
        );
    }

    public function testExtensionWithFullPath(): void
    {
        $this->assertSame(
            'ext',
            Path::extension('/sub/dir/file.ext')
        );
    }

    public function testExtensionWithMultipleExtensions(): void
    {
        $this->assertSame(
            'ext',
            Path::extension('dir/file.tst.ext')
        );
    }

    public function testExtensionWithNoExtension(): void
    {
        $this->assertSame(
            '',
            Path::extension('file')
        );
    }

    public function testExtensionWithPath(): void
    {
        $this->assertSame(
            'ext',
            Path::extension('dir/file.ext')
        );
    }
}
