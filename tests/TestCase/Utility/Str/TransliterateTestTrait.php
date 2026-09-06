<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait TransliterateTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function transliterateProvider(): array
    {
        return [
            'accents' => ['äëïöü', 'aeiou'],
            'string' => ['This is a test string', 'This is a test string'],
        ];
    }

    #[DataProvider('transliterateProvider')]
    public function testTransliterate(string $string, string $expected): void
    {
        $this->assertSame(
            $expected,
            Str::transliterate($string)
        );
    }
}
