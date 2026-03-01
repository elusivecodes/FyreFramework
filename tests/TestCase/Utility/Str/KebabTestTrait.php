<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait KebabTestTrait
{
    public function testKebabFromCamel(): void
    {
        $this->assertSame(
            'this-is-a-test-string',
            Str::kebab('thisIsATestString')
        );
    }

    public function testKebabFromPascal(): void
    {
        $this->assertSame(
            'this-is-a-test-string',
            Str::kebab('ThisIsATestString')
        );
    }

    public function testKebabWithConsecutiveSpaces(): void
    {
        $this->assertSame(
            'this-is-a-test-string',
            Str::kebab('This is a test   string')
        );
    }

    public function testKebabWithString(): void
    {
        $this->assertSame(
            'this-is-a-test-string',
            Str::kebab('This is a test string')
        );
    }
}
