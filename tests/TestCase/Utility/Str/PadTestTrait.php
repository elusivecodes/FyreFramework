<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait PadTestTrait
{
    /**
     * @return array<string, array{string, int, string}>
     */
    public static function padProvider(): array
    {
        return [
            'above length' => ['This is a test string', 10, 'This is a test string'],
            'below length' => ['This is a test string', 25, '  This is a test string  '],
            'empty string' => ['', 1, ' '],
        ];
    }

    #[DataProvider('padProvider')]
    public function testPad(string $string, int $length, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::pad($string, $length)
        );
    }

    public function testPadWithPadding(): void
    {
        $this->assertSame(
            '__This is a test string__',
            Str::pad('This is a test string', 25, '_')
        );
    }
}
