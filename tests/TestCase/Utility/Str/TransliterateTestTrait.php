<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait TransliterateTestTrait
{
    public function testTransliterateWithAccents(): void
    {
        $this->assertSame(
            'aeiou',
            Str::transliterate('äëïöü')
        );
    }

    public function testTransliterateWithString(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::transliterate('This is a test string')
        );
    }
}
