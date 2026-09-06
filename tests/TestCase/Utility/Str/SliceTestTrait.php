<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait SliceTestTrait
{
    /**
     * @return array<string, array{string, int, string}>
     */
    public static function sliceProvider(): array
    {
        return [
            'empty string' => ['', 10, ''],
            'negative start' => ['This is a test string', -11, 'test string'],
            'out of bounds start' => ['This is a test string', 21, ''],
            'positive start' => ['This is a test string', 10, 'test string'],
        ];
    }

    /**
     * @return array<string, array{string, int, int, string}>
     */
    public static function sliceWithLengthProvider(): array
    {
        return [
            'negative length' => ['This is a test string', 10, -7, 'test'],
            'positive length' => ['This is a test string', 10, 4, 'test'],
        ];
    }

    #[DataProvider('sliceProvider')]
    public function testSlice(string $string, int $start, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::slice($string, $start)
        );
    }

    #[DataProvider('sliceWithLengthProvider')]
    public function testSliceWithLength(string $string, int $start, int|null $length, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::slice($string, $start, $length)
        );
    }
}
