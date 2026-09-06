<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait UpperTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function upperProvider(): array
    {
        return [
            'lower case' => ['this is a test string', 'THIS IS A TEST STRING'],
            'upper case' => ['THIS IS A TEST STRING', 'THIS IS A TEST STRING'],
        ];
    }

    #[DataProvider('upperProvider')]
    public function testUpper(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::upper($string)
        );
    }
}
