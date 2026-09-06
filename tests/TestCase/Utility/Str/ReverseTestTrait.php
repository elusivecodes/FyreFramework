<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait ReverseTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function reverseProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'string' => ['This is a test string', 'gnirts tset a si sihT'],
        ];
    }

    #[DataProvider('reverseProvider')]
    public function testReverse(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::reverse($string)
        );
    }
}
