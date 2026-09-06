<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function startProvider(): array
    {
        return [
            'empty string' => ['This is a test string', '', 'This is a test string'],
            'match' => ['This is a test string', 'This is a ', 'This is a test string'],
            'without match' => ['test string', 'This is a ', 'This is a test string'],
        ];
    }

    #[DataProvider('startProvider')]
    public function testStart(string $string, string $search, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::start($string, $search)
        );
    }
}
