<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait BeforeLastTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function beforeLastProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 'This is a test string'],
            'match' => ['This is a test string', ' test ', 'This is a'],
            'multiple matches' => ['This is a test test string', ' test ', 'This is a test'],
            'without match' => ['This is a test string', 'invalid', 'This is a test string'],
        ];
    }

    #[DataProvider('beforeLastProvider')]
    public function testBeforeLast(string $string, string $search, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::beforeLast($string, $search)
        );
    }
}
