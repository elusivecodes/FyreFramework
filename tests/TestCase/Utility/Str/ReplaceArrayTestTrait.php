<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ReplaceArrayTestTrait
{
    /**
     * @return array<string, array{string, string, array<int, string>, string}>
     */
    public static function replaceArrayProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', ['new'], 'This is a test string'],
            'excess replacements' => ['This is a test string', 'test', ['new', 'different'], 'This is a new string'],
            'match' => ['This is a test string', 'test', ['new'], 'This is a new string'],
            'missing replacements' => ['This is a test test string', 'test', ['new'], 'This is a new  string'],
            'multiple matches' => ['This is a test test string', 'test', ['new', 'different'], 'This is a new different string'],
            'without match' => ['This is a test string', 'invalid', ['new'], 'This is a test string'],
        ];
    }

    /**
     * @param array<int, string> $replacements
     */
    #[DataProvider('replaceArrayProvider')]
    public function testReplaceArray(string $string, string $search, array $replacements, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::replaceArray($string, $search, $replacements)
        );
    }
}
