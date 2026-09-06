<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ReplaceAtTestTrait
{
    /**
     * @return array<string, array{string, string, int, string}>
     */
    public static function replaceAtProvider(): array
    {
        return [
            'negative position' => ['This is a test string', 'new ', -11, 'This is a new test string'],
            'positive position' => ['This is a test string', 'new ', 10, 'This is a new test string'],
        ];
    }

    /**
     * @return array<string, array{string, string, int, int, string}>
     */
    public static function replaceAtWithLengthProvider(): array
    {
        return [
            'negative length' => ['This is a test string', 'new', 10, -7, 'This is a new string'],
            'positive length' => ['This is a test string', 'new', 10, 4, 'This is a new string'],
        ];
    }

    #[DataProvider('replaceAtProvider')]
    public function testReplaceAt(string $string, string $replace, int $position, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::replaceAt($string, $replace, $position)
        );
    }

    #[DataProvider('replaceAtWithLengthProvider')]
    public function testReplaceAtWithLength(string $string, string $replace, int $position, int $length, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::replaceAt($string, $replace, $position, $length)
        );
    }
}
