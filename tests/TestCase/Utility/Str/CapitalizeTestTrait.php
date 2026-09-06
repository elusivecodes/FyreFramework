<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait CapitalizeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function capitalizeProvider(): array
    {
        return [
            'lower case' => ['this is a test string', 'This is a test string'],
            'uppercase' => ['THIS IS A TEST STRING', 'This is a test string'],
        ];
    }

    #[DataProvider('capitalizeProvider')]
    public function testCapitalize(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::capitalize($string)
        );
    }
}
