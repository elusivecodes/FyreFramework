<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait LengthTestTrait
{
    /**
     * @return array<string, array{string, int}>
     */
    public static function lengthProvider(): array
    {
        return [
            'empty string' => ['', 0],
            'string' => ['This is a test string', 21],
        ];
    }

    #[DataProvider('lengthProvider')]
    public function testLength(string $string, int $expected): void
    {
        $this->assertSame(
            $expected,
            Str::length($string)
        );
    }
}
