<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait RepeatTestTrait
{
    /**
     * @return array<string, array{string, int, string}>
     */
    public static function repeatProvider(): array
    {
        return [
            'one count' => ['This is a test string', 1, 'This is a test string'],
            'count' => ['This is a test string', 3, 'This is a test stringThis is a test stringThis is a test string'],
            'empty string' => ['', 3, ''],
            'zero count' => ['This is a test string', 0, ''],
        ];
    }

    #[DataProvider('repeatProvider')]
    public function testRepeat(string $string, int $count, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::repeat($string, $count)
        );
    }
}
