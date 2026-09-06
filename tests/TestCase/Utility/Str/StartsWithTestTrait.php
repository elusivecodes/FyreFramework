<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartsWithTestTrait
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function startsWithProvider(): array
    {
        return [
            'empty string' => ['This is a test string', '', false],
            'match' => ['This is a test string', 'This is a ', true],
            'without match' => ['test string', 'This is a ', false],
        ];
    }

    #[DataProvider('startsWithProvider')]
    public function testStartsWith(string $string, string $search, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::startsWith($string, $search)
        );
    }
}
