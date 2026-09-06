<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function endProvider(): array
    {
        return [
            'empty string' => ['This is a test string', '', 'This is a test string'],
            'match' => ['This is a test string', ' a test string', 'This is a test string'],
            'without match' => ['This is a ', 'test string', 'This is a test string'],
        ];
    }

    #[DataProvider('endProvider')]
    public function testEnd(string $string, string $search, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::end($string, $search)
        );
    }
}
