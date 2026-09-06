<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait EndsWithTestTrait
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function endsWithProvider(): array
    {
        return [
            'empty string' => ['This is a test string', '', false],
            'match' => ['This is a test string', ' a test string', true],
            'without match' => ['This is a ', 'test string', false],
        ];
    }

    #[DataProvider('endsWithProvider')]
    public function testEndsWith(string $string, string $search, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::endsWith($string, $search)
        );
    }
}
