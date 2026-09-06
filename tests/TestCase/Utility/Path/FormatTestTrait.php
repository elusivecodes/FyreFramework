<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatTestTrait
{
    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function formatProvider(): array
    {
        return [
            'value' => [['dirname' => 'sub/dir', 'basename' => 'file.ext'], 'sub/dir/file.ext'],
            'empty dir name' => [['basename' => 'file.ext'], 'file.ext'],
            'empty file name' => [['dirname' => 'sub/dir'], 'sub/dir'],
        ];
    }

    /**
     * @param array<string, string> $pathInfo
     */
    #[DataProvider('formatProvider')]
    public function testFormat(array $pathInfo, string $expected): void
    {
        $this->assertSame(
            $expected,
            Path::format($pathInfo)
        );
    }
}
