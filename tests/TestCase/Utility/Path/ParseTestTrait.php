<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait ParseTestTrait
{
    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function parseProvider(): array
    {
        return [
            'deep path' => ['sub/dir/file.ext', ['dirname' => 'sub/dir', 'basename' => 'file.ext', 'extension' => 'ext', 'filename' => 'file']],
            'empty string' => ['', ['basename' => '', 'filename' => '']],
            'file name' => ['file.ext', ['dirname' => '.', 'basename' => 'file.ext', 'extension' => 'ext', 'filename' => 'file']],
            'full path' => ['/sub/dir/file.ext', ['dirname' => '/sub/dir', 'basename' => 'file.ext', 'extension' => 'ext', 'filename' => 'file']],
            'multiple extensions' => ['dir/file.tst.ext', ['dirname' => 'dir', 'basename' => 'file.tst.ext', 'extension' => 'ext', 'filename' => 'file.tst']],
            'no extension' => ['dir/file', ['dirname' => 'dir', 'basename' => 'file', 'filename' => 'file']],
            'path' => ['dir/file.ext', ['dirname' => 'dir', 'basename' => 'file.ext', 'extension' => 'ext', 'filename' => 'file']],
        ];
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('parseProvider')]
    public function testParse(string $path, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Path::parse($path)
        );
    }
}
