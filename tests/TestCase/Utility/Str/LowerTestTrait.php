<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait LowerTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function lowerProvider(): array
    {
        return [
            'lower case' => ['this is a test string', 'this is a test string'],
            'upper case' => ['THIS IS A TEST STRING', 'this is a test string'],
        ];
    }

    #[DataProvider('lowerProvider')]
    public function testLower(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::lower($string)
        );
    }
}
