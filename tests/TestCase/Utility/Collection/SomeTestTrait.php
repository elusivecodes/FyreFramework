<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait SomeTestTrait
{
    public function testSome(): void
    {
        $this->assertTrue(
            Collection::range(1, 10)
                ->some(static fn(int $value, int $key): bool => $value === 5)
        );
    }

    public function testSomeEmpty(): void
    {
        $this->assertFalse(
            Collection::empty()
                ->some(static fn(): bool => false)
        );
    }

    public function testSomeFalse(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)
                ->some(static fn(int $value, int $key): bool => $value === 11)
        );
    }
}
