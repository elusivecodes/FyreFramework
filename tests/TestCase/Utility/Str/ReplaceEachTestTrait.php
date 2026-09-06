<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ReplaceEachTestTrait
{
    /**
     * @return array<string, array{string, array<string, string>, string}>
     */
    public static function replaceEachProvider(): array
    {
        return [
            'matches' => ['This is a test string', ['test' => 'new', 'string' => 'phrase'], 'This is a new phrase'],
            'multiple matches' => ['This is a test test string', ['test' => 'new', 'string' => 'phrase'], 'This is a new new phrase'],
            'without match' => ['This is a test string', ['invalid' => 'new', 'sentence' => 'phrase'], 'This is a test string'],
        ];
    }

    /**
     * @param array<string, string> $replacements
     */
    #[DataProvider('replaceEachProvider')]
    public function testReplaceEach(string $string, array $replacements, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::replaceEach($string, $replacements)
        );
    }
}
