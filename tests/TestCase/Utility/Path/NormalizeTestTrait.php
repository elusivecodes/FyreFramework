<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait NormalizeTestTrait
{
    public function testNormalizeWithCurrentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::normalize('./sub/dir/file.ext')
        );
    }

    public function testNormalizeWithDeepPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::normalize('sub/dir/file.ext')
        );
    }

    public function testNormalizeWithEmptyString(): void
    {
        $this->assertSame(
            '.',
            Path::normalize('')
        );
    }

    public function testNormalizeWithFileName(): void
    {
        $this->assertSame(
            'file.ext',
            Path::normalize('file.ext')
        );
    }

    public function testNormalizeWithFullPath(): void
    {
        $this->assertSame(
            '/sub/dir/file.ext',
            Path::normalize('/sub/dir/file.ext')
        );
    }

    public function testNormalizeWithNoArguments(): void
    {
        $this->assertSame(
            '.',
            Path::normalize()
        );
    }

    public function testNormalizeWithParentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::normalize('test/../sub/dir/file.ext')
        );
    }

    public function testNormalizeWithPath(): void
    {
        $this->assertSame(
            'dir/file.ext',
            Path::normalize('dir/file.ext')
        );
    }

    public function testNormalizeWithTrailingSlash(): void
    {
        $this->assertSame(
            '/sub/dir/',
            Path::normalize('/sub/dir/')
        );
    }
}
