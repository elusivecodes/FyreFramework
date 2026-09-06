<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait CamelTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function camelProvider(): array
    {
        return [
            'kebab' => ['this-is-a-test-string', 'thisIsATestString'],
            'pascal' => ['ThisIsATestString', 'thisIsATestString'],
            'snake' => ['this_is_a_test_string', 'thisIsATestString'],
            'consecutive spaces' => ['This is a test   string', 'thisIsATestString'],
            'string' => ['This is a test string', 'thisIsATestString'],
        ];
    }

    #[DataProvider('camelProvider')]
    public function testCamel(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::camel($string)
        );
    }
}
