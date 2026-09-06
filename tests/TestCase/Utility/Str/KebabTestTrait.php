<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait KebabTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function kebabProvider(): array
    {
        return [
            'camel' => ['thisIsATestString', 'this-is-a-test-string'],
            'pascal' => ['ThisIsATestString', 'this-is-a-test-string'],
            'consecutive spaces' => ['This is a test   string', 'this-is-a-test-string'],
            'string' => ['This is a test string', 'this-is-a-test-string'],
        ];
    }

    #[DataProvider('kebabProvider')]
    public function testKebab(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::kebab($string)
        );
    }
}
