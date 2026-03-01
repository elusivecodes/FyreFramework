<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FirstTestTrait
{
    public function testFirst(): void
    {
        $this->assertSame(
            1,
            Arr::first([1, 2, 3, 4, 5]),
        );
    }

    public function testFirstEmpty(): void
    {
        $this->assertSame(
            null,
            Arr::first([])
        );
    }
}
