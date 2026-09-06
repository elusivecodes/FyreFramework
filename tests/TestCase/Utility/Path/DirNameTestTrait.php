<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait DirNameTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function dirNameProvider(): array
    {
        return [
            'deep path' => ['sub/dir/file.ext', 'sub/dir'],
            'empty string' => ['', ''],
            'file name' => ['file.ext', '.'],
            'full path' => ['/sub/dir/file.ext', '/sub/dir'],
            'multiple extensions' => ['dir/file.tst.ext', 'dir'],
            'no extension' => ['dir/file', 'dir'],
            'path' => ['dir/file.ext', 'dir'],
        ];
    }

    #[DataProvider('dirNameProvider')]
    public function testDirName(string $path, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::dirName($path)
        );
    }
}
