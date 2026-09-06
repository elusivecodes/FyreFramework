<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait LastIndexOfTestTrait
{
    /**
     * @return array<string, array{string, string, int}>
     */
    public static function lastIndexOfProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 21],
            'match' => ['This is a test string', ' test ', 9],
            'multiple matches' => ['This is a test test string', ' test ', 14],
            'without match' => ['This is a test string', 'invalid', -1],
        ];
    }

    /**
     * @return array<string, array{string, string, int, int}>
     */
    public static function lastIndexOfWithStartProvider(): array
    {
        return [
            'negative start' => ['This is a test test string', ' test ', -13, 9],
            'positive start' => ['This is a test test string', ' test ', 10, 14],
        ];
    }

    #[DataProvider('lastIndexOfProvider')]
    public function testLastIndexOf(string $string, string $search, int $expected): void
    {
        $this->assertSame(
            $expected,
            Str::lastIndexOf($string, $search)
        );
    }

    #[DataProvider('lastIndexOfWithStartProvider')]
    public function testLastIndexOfWithStart(string $string, string $search, int $start, int $expected): void
    {
        $this->assertSame(
            $expected,
            Str::lastIndexOf($string, $search, $start)
        );
    }
}
