<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait PascalTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function pascalProvider(): array
    {
        return [
            'camel' => ['thisIsATestString', 'ThisIsATestString'],
            'kebab' => ['this-is-a-test-string', 'ThisIsATestString'],
            'snake' => ['this_is_a_test_string', 'ThisIsATestString'],
            'consecutive spaces' => ['This is a test   string', 'ThisIsATestString'],
            'string' => ['This is a test string', 'ThisIsATestString'],
        ];
    }

    #[DataProvider('pascalProvider')]
    public function testPascal(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::pascal($string)
        );
    }
}
