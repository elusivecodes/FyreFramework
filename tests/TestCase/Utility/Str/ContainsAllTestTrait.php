<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ContainsAllTestTrait
{
    /**
     * @return array<string, array{string, array<int, string>, bool}>
     */
    public static function containsAllProvider(): array
    {
        return [
            'empty string' => ['This is a test string', [''], true],
            'matches' => ['This is a string', ['string', 'is'], true],
            'without match' => ['This is a string', ['test', 'value'], false],
            'single match' => ['This is a string', ['is', 'value'], false],
        ];
    }

    /**
     * @param array<int, string> $searches
     */
    #[DataProvider('containsAllProvider')]
    public function testContainsAll(string $string, array $searches, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::containsAll($string, $searches)
        );
    }
}
