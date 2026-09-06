<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait PadStartTestTrait
{
    /**
     * @return array<string, array{string, int, string}>
     */
    public static function padStartProvider(): array
    {
        return [
            'above length' => ['This is a test string', 10, 'This is a test string'],
            'below length' => ['This is a test string', 23, '  This is a test string'],
            'empty string' => ['', 1, ' '],
        ];
    }

    #[DataProvider('padStartProvider')]
    public function testPadStart(string $string, int $length, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::padStart($string, $length)
        );
    }

    public function testPadStartWithPadding(): void
    {
        $this->assertSame(
            '__This is a test string',
            Str::padStart('This is a test string', 23, '_')
        );
    }
}
