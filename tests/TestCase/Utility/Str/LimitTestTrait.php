<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait LimitTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function limitProvider(): array
    {
        return [
            'exceeded' => ['This is a test string that is designed specifically to contain enough words to go above the default limit of 100 characters.', 'This is a test string that is designed specifically to contain enough words to go above the default …'],
            'not reached' => ['This is a test string', 'This is a test string'],
        ];
    }

    #[DataProvider('limitProvider')]
    public function testLimit(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::limit($string)
        );
    }

    public function testLimitWithAppend(): void
    {
        $this->assertSame(
            'This is a _',
            Str::limit('This is a test string', 10, '_')
        );
    }

    public function testLimitWithLimit(): void
    {
        $this->assertSame(
            'This is a …',
            Str::limit('This is a test string', 10)
        );
    }
}
