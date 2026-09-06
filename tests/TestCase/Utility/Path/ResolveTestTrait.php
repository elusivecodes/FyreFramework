<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

use function getcwd;

trait ResolveTestTrait
{
    /**
     * @return array<string, array{array<int, string>, string}>
     */
    public static function resolveProvider(): array
    {
        return [
            'current path' => [['.', 'sub', 'dir', 'file.ext'], 'sub/dir/file.ext'],
            'deep dir' => [['sub/dir', 'file.ext'], 'sub/dir/file.ext'],
            'dir' => [['dir', 'file.ext'], 'dir/file.ext'],
            'dirs' => [['sub', 'dir', 'file.ext'], 'sub/dir/file.ext'],
            'empty string' => [[''], '.'],
            'file name' => [['file.ext'], 'file.ext'],
            'full path' => [['/sub', 'dir', 'file.ext'], '/sub/dir/file.ext'],
            'full paths' => [['/sub', '/dir', 'file.ext'], '/dir/file.ext'],
            'parent path' => [['test', '..', 'sub/dir', 'file.ext'], 'sub/dir/file.ext'],
        ];
    }

    /**
     * @param array<int, string> $paths
     */
    #[DataProvider('resolveProvider')]
    public function testResolve(array $paths, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::resolve(...$paths)
        );
    }

    public function testResolveWithNoArguments(): void
    {
        $this->assertSame(
            getcwd(),
            Path::resolve()
        );
    }
}
