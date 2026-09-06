<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait AfterLastTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function afterLastProvider(): array
    {
        return [
            'empty search' => ['This is a test string', '', 'This is a test string'],
            'match' => ['This is a test string', ' test ', 'string'],
            'multiple matches' => ['This is a test test string', ' test ', 'string'],
            'without match' => ['This is a test string', 'invalid', 'This is a test string'],
        ];
    }

    #[DataProvider('afterLastProvider')]
    public function testAfterLast(string $string, string $search, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::afterLast($string, $search)
        );
    }
}
