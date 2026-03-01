<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait JoinTestTrait
{
    public function testJoinWithCurrentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::join('.', 'sub', 'dir', 'file.ext')
        );
    }

    public function testJoinWithDeepDir(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::join('sub/dir', 'file.ext')
        );
    }

    public function testJoinWithDir(): void
    {
        $this->assertSame(
            'dir/file.ext',
            Path::join('dir', 'file.ext')
        );
    }

    public function testJoinWithDirs(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::join('sub', 'dir', 'file.ext')
        );
    }

    public function testJoinWithEmptyString(): void
    {
        $this->assertSame(
            '.',
            Path::join('')
        );
    }

    public function testJoinWithFileName(): void
    {
        $this->assertSame(
            'file.ext',
            Path::join('file.ext')
        );
    }

    public function testJoinWithFullPath(): void
    {
        $this->assertSame(
            '/sub/dir/file.ext',
            Path::join('/sub', 'dir', 'file.ext')
        );
    }

    public function testJoinWithNoArguments(): void
    {
        $this->assertSame(
            '.',
            Path::join()
        );
    }

    public function testJoinWithParentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::join('test', '..', 'sub/dir', 'file.ext')
        );
    }

    public function testJoinWithTrailingSlash(): void
    {
        $this->assertSame(
            '/sub/dir/',
            Path::join('/sub/', 'dir/')
        );
    }
}
