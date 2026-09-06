<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait JoinTestTrait
{
    /**
     * @return array<string, array{array<int, string>, string}>
     */
    public static function joinProvider(): array
    {
        return [
            'current path' => [['.', 'sub', 'dir', 'file.ext'], 'sub/dir/file.ext'],
            'deep dir' => [['sub/dir', 'file.ext'], 'sub/dir/file.ext'],
            'dir' => [['dir', 'file.ext'], 'dir/file.ext'],
            'dirs' => [['sub', 'dir', 'file.ext'], 'sub/dir/file.ext'],
            'empty string' => [[''], '.'],
            'file name' => [['file.ext'], 'file.ext'],
            'full path' => [['/sub', 'dir', 'file.ext'], '/sub/dir/file.ext'],
            'parent path' => [['test', '..', 'sub/dir', 'file.ext'], 'sub/dir/file.ext'],
            'trailing slash' => [['/sub/', 'dir/'], '/sub/dir/'],
        ];
    }

    /**
     * @param array<int, string> $paths
     */
    #[DataProvider('joinProvider')]
    public function testJoin(array $paths, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::join(...$paths)
        );
    }

    public function testJoinWithNoArguments(): void
    {
        $this->assertSame(
            '.',
            Path::join()
        );
    }
}
