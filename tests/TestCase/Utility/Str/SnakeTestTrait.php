<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait SnakeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function snakeProvider(): array
    {
        return [
            'camel' => ['thisIsATestString', 'this_is_a_test_string'],
            'pascal' => ['ThisIsATestString', 'this_is_a_test_string'],
            'consecutive spaces' => ['This is a test   string', 'this_is_a_test_string'],
            'string' => ['This is a test string', 'this_is_a_test_string'],
        ];
    }

    #[DataProvider('snakeProvider')]
    public function testSnake(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::snake($string)
        );
    }
}
