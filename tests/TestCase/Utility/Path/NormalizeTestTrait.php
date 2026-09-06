<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait NormalizeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function normalizeProvider(): array
    {
        return [
            'current path' => ['./sub/dir/file.ext', 'sub/dir/file.ext'],
            'current path only' => ['./', '.'],
            'deep path' => ['sub/dir/file.ext', 'sub/dir/file.ext'],
            'empty string' => ['', '.'],
            'file name' => ['file.ext', 'file.ext'],
            'full path' => ['/sub/dir/file.ext', '/sub/dir/file.ext'],
            'parent path' => ['test/../sub/dir/file.ext', 'sub/dir/file.ext'],
            'parent path above root' => ['/test/../../sub/dir/file.ext', '/sub/dir/file.ext'],
            'parent path before relative path' => ['test/../../sub/dir/file.ext', '../sub/dir/file.ext'],
            'path' => ['dir/file.ext', 'dir/file.ext'],
            'trailing slash' => ['/sub/dir/', '/sub/dir/'],
        ];
    }

    #[DataProvider('normalizeProvider')]
    public function testNormalize(string $path, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::normalize($path)
        );
    }

    public function testNormalizeWithNoArguments(): void
    {
        $this->assertSame(
            '.',
            Path::normalize()
        );
    }
}
