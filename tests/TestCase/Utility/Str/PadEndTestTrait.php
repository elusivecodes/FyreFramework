<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait PadEndTestTrait
{
    /**
     * @return array<string, array{string, int, string}>
     */
    public static function padEndProvider(): array
    {
        return [
            'above length' => ['This is a test string', 10, 'This is a test string'],
            'below length' => ['This is a test string', 23, 'This is a test string  '],
            'empty string' => ['', 1, ' '],
        ];
    }

    #[DataProvider('padEndProvider')]
    public function testPadEnd(string $string, int $length, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::padEnd($string, $length)
        );
    }

    public function testPadEndWithPadding(): void
    {
        $this->assertSame(
            'This is a test string__',
            Str::padEnd('This is a test string', 23, '_')
        );
    }
}
