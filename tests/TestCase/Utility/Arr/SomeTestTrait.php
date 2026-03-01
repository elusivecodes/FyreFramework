<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait SomeTestTrait
{
    public function testSome(): void
    {
        $this->assertTrue(
            Arr::some(
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value === 5
            )
        );
    }

    public function testSomeEmpty(): void
    {
        $this->assertFalse(
            Arr::some(
                [],
                static fn(int $value, int $key): bool => false
            )
        );
    }

    public function testSomeFalse(): void
    {
        $this->assertFalse(
            Arr::some(
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                static fn(int $value, int $key): bool => $value === 11
            )
        );
    }
}
