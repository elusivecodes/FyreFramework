<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait LastTestTrait
{
    public function testLast(): void
    {
        $this->assertSame(
            5,
            Arr::last([1, 2, 3, 4, 5]),
        );
    }

    public function testLastEmpty(): void
    {
        $this->assertSame(
            null,
            Arr::last([])
        );
    }
}
