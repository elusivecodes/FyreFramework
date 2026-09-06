<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ContainsTestTrait
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function containsProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', true],
            'match' => ['This is a test string', 'test', true],
            'match at start' => ['This is a test string', 'This', true],
            'no match' => ['This is a string', 'test', false],
        ];
    }

    #[DataProvider('containsProvider')]
    public function testContains(string $string, string $search, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::contains($string, $search)
        );
    }
}
