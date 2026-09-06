<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait BaseNameTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function baseNameProvider(): array
    {
        return [
            'deep path' => ['sub/dir/file.ext', 'file.ext'],
            'empty string' => ['', ''],
            'file name' => ['file.ext', 'file.ext'],
            'full path' => ['/sub/dir/file.ext', 'file.ext'],
            'multiple extensions' => ['dir/file.tst.ext', 'file.tst.ext'],
            'no extension' => ['dir/file', 'file'],
            'path' => ['dir/file.ext', 'file.ext'],
        ];
    }

    #[DataProvider('baseNameProvider')]
    public function testBaseName(string $path, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::baseName($path)
        );
    }
}
