<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait SlugTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function slugProvider(): array
    {
        return [
            'camel case' => ['thisIsATestString', 'this_is_a_test_string'],
            'pascal case' => ['ThisIsATestString', 'this_is_a_test_string'],
            'snake case' => ['this_is_a_test_string', 'this_is_a_test_string'],
            'consecutive spaces' => ['This  is  a  test  string', 'this_is_a_test_string'],
            'spaces' => ['This is a test string', 'this_is_a_test_string'],
        ];
    }

    #[DataProvider('slugProvider')]
    public function testSlug(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::slug($string)
        );
    }

    public function testSlugWithDelimiter(): void
    {
        $this->assertSame(
            'this+is+a+test+string',
            Str::slug('This is a test string', '+')
        );
    }
}
