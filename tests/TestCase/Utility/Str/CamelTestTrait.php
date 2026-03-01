<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait CamelTestTrait
{
    public function testCamelFromKebab(): void
    {
        $this->assertSame(
            'thisIsATestString',
            Str::camel('this-is-a-test-string')
        );
    }

    public function testCamelFromPascal(): void
    {
        $this->assertSame(
            'thisIsATestString',
            Str::camel('ThisIsATestString')
        );
    }

    public function testCamelFromSnake(): void
    {
        $this->assertSame(
            'thisIsATestString',
            Str::camel('this_is_a_test_string')
        );
    }

    public function testCamelWithConsecutiveSpaces(): void
    {
        $this->assertSame(
            'thisIsATestString',
            Str::camel('This is a test   string')
        );
    }

    public function testCamelWithString(): void
    {
        $this->assertSame(
            'thisIsATestString',
            Str::camel('This is a test string')
        );
    }
}
