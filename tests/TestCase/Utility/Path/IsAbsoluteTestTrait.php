<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;
use PHPUnit\Framework\Attributes\DataProvider;

trait IsAbsoluteTestTrait
{
    /**
     * @return array<string, array{string, bool}>
     */
    public static function isAbsoluteProvider(): array
    {
        return [
            'value' => ['/path/to/file', true],
            'relative' => ['path/to/file', false],
            'windows drive letter' => ['C:\path\to\file', true],
            'windows relative drive path' => ['C:path\to\file', false],
        ];
    }

    #[DataProvider('isAbsoluteProvider')]
    public function testIsAbsolute(string $path, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Path::isAbsolute($path)
        );
    }
}
