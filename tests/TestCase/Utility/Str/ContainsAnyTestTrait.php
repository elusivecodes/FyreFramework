<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ContainsAnyTestTrait
{
    /**
     * @return array<string, array{string, array<int, string>, bool}>
     */
    public static function containsAnyProvider(): array
    {
        return [
            'empty string' => ['This is a test string', [''], true],
            'match' => ['This is a string', ['test', 'is'], true],
            'match at start' => ['This is a test string', ['This'], true],
            'without match' => ['This is a string', ['test', 'value'], false],
        ];
    }

    /**
     * @param array<int, string> $searches
     */
    #[DataProvider('containsAnyProvider')]
    public function testContainsAny(string $string, array $searches, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::containsAny($string, $searches)
        );
    }
}
