<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait AfterTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function afterProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 'This is a test string'],
            'match' => ['This is a test string', ' test ', 'string'],
            'multiple matches' => ['This is a test test string', ' test ', 'test string'],
            'without match' => ['This is a test string', 'invalid', 'This is a test string'],
        ];
    }

    #[DataProvider('afterProvider')]
    public function testAfter(string $string, string $search, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::after($string, $search)
        );
    }
}
