<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait EscapeTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function escapeProvider(): array
    {
        return [
            'ampersand' => ['&', '&amp;'],
            'double quote' => ['"', '&quot;'],
            'greater than' => ['>', '&gt;'],
            'less than' => ['<', '&lt;'],
            'single quote' => ['\'', '&apos;'],
            'string' => ['This is a test string', 'This is a test string'],
        ];
    }

    #[DataProvider('escapeProvider')]
    public function testEscape(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::escape($string)
        );
    }
}
