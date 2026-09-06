<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait IndexOfTestTrait
{
    /**
     * @return array<string, array{string, string, int}>
     */
    public static function indexOfProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 0],
            'match' => ['This is a test string', ' test ', 9],
            'multiple matches' => ['This is a test test string', ' test ', 9],
            'without match' => ['This is a test string', 'invalid', -1],
        ];
    }

    /**
     * @return array<string, array{string, string, int, int}>
     */
    public static function indexOfWithStartProvider(): array
    {
        return [
            'negative start' => ['This is a test test string', ' test ', -13, 14],
            'positive start' => ['This is a test test string', ' test ', 10, 14],
        ];
    }

    #[DataProvider('indexOfProvider')]
    public function testIndexOf(string $string, string $search, int $expected): void
    {
        $this->assertSame(
            $expected,
            Str::indexOf($string, $search)
        );
    }

    #[DataProvider('indexOfWithStartProvider')]
    public function testIndexOfWithStart(string $string, string $search, int $start, int $expected): void
    {
        $this->assertSame(
            $expected,
            Str::indexOf($string, $search, $start)
        );
    }
}
