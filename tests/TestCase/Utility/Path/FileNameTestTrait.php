<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait FileNameTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function fileNameProvider(): array
    {
        return [
            'deep path' => ['sub/dir/file.ext', 'file'],
            'empty string' => ['', ''],
            'file name' => ['file.ext', 'file'],
            'full path' => ['/sub/dir/file.ext', 'file'],
            'multiple extensions' => ['dir/file.tst.ext', 'file.tst'],
            'no extension' => ['dir/file', 'file'],
            'path' => ['dir/file.ext', 'file'],
        ];
    }

    #[DataProvider('fileNameProvider')]
    public function testFileName(string $path, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::fileName($path)
        );
    }
}
