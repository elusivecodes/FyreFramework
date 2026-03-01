<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait BaseNameTestTrait
{
    public function testBaseNameWithDeepPath(): void
    {
        $this->assertSame(
            'file.ext',
            Path::baseName('sub/dir/file.ext')
        );
    }

    public function testBaseNameWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Path::baseName('')
        );
    }

    public function testBaseNameWithFileName(): void
    {
        $this->assertSame(
            'file.ext',
            Path::baseName('file.ext')
        );
    }

    public function testBaseNameWithFullPath(): void
    {
        $this->assertSame(
            'file.ext',
            Path::baseName('/sub/dir/file.ext')
        );
    }

    public function testBaseNameWithMultipleExtensions(): void
    {
        $this->assertSame(
            'file.tst.ext',
            Path::baseName('dir/file.tst.ext')
        );
    }

    public function testBaseNameWithNoExtension(): void
    {
        $this->assertSame(
            'file',
            Path::baseName('dir/file')
        );
    }

    public function testBaseNameWithPath(): void
    {
        $this->assertSame(
            'file.ext',
            Path::baseName('dir/file.ext')
        );
    }
}
