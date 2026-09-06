<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait ExtensionTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function extensionProvider(): array
    {
        return [
            'deep path' => ['sub/dir/file.ext', 'ext'],
            'empty string' => ['', ''],
            'file name' => ['file.ext', 'ext'],
            'full path' => ['/sub/dir/file.ext', 'ext'],
            'multiple extensions' => ['dir/file.tst.ext', 'ext'],
            'no extension' => ['file', ''],
            'path' => ['dir/file.ext', 'ext'],
        ];
    }

    #[DataProvider('extensionProvider')]
    public function testExtension(string $path, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::extension($path)
        );
    }
}
