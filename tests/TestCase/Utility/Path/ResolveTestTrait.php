<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

use function getcwd;

trait ResolveTestTrait
{
    public function testResolveWithCurrentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::resolve('.', 'sub', 'dir', 'file.ext')
        );
    }

    public function testResolveWithDeepDir(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::resolve('sub/dir', 'file.ext')
        );
    }

    public function testResolveWithDir(): void
    {
        $this->assertSame(
            'dir/file.ext',
            Path::resolve('dir', 'file.ext')
        );
    }

    public function testResolveWithDirs(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::resolve('sub', 'dir', 'file.ext')
        );
    }

    public function testResolveWithEmptyString(): void
    {
        $this->assertSame(
            '.',
            Path::resolve('')
        );
    }

    public function testResolveWithFileName(): void
    {
        $this->assertSame(
            'file.ext',
            Path::resolve('file.ext')
        );
    }

    public function testResolveWithFullPath(): void
    {
        $this->assertSame(
            '/sub/dir/file.ext',
            Path::resolve('/sub', 'dir', 'file.ext')
        );
    }

    public function testResolveWithFullPaths(): void
    {
        $this->assertSame(
            '/dir/file.ext',
            Path::resolve('/sub', '/dir', 'file.ext')
        );
    }

    public function testResolveWithNoArguments(): void
    {
        $this->assertSame(
            getcwd(),
            Path::resolve()
        );
    }

    public function testResolveWithParentPath(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::resolve('test', '..', 'sub/dir', 'file.ext')
        );
    }
}
