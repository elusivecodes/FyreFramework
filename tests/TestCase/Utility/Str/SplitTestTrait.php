<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait SplitTestTrait
{
    /**
     * @return array<string, array{string, string, array<int, string>}>
     */
    public static function splitProvider(): array
    {
        return [
            'empty string' => ['', ' ', ['']],
            'space' => ['This is a test string', ' ', ['This', 'is', 'a', 'test', 'string']],
            'string' => ['This is a test string', ' test ', ['This is a', 'string']],
        ];
    }

    /**
     * @return array<string, array{string, string, int, array<int, string>}>
     */
    public static function splitWithLimitProvider(): array
    {
        return [
            'negative limit' => ['This is a test string', ' ', -1, ['This', 'is', 'a', 'test']],
            'positive limit' => ['This is a test string', ' ', 3, ['This', 'is', 'a test string']],
            'zero limit' => ['This is a test string', ' ', 0, ['This is a test string']],
        ];
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('splitProvider')]
    public function testSplit(string $string, string $delimiter, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Str::split($string, $delimiter)
        );
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('splitWithLimitProvider')]
    public function testSplitWithLimit(string $string, string $delimiter, int $limit, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Str::split($string, $delimiter, $limit)
        );
    }
}
