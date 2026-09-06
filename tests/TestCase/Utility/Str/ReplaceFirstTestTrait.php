<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ReplaceFirstTestTrait
{
    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function replaceFirstProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 'new', 'This is a test string'],
            'match' => ['This is a test string', 'test', 'new', 'This is a new string'],
            'multiple matches' => ['This is a test test string', 'test', 'new', 'This is a new test string'],
            'without match' => ['This is a test string', 'invalid', 'new', 'This is a test string'],
        ];
    }

    #[DataProvider('replaceFirstProvider')]
    public function testReplaceFirst(string $string, string $search, string $replace, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::replaceFirst($string, $search, $replace)
        );
    }
}
