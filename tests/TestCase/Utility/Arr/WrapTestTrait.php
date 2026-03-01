<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait WrapTestTrait
{
    public function testWrap(): void
    {
        $this->assertSame(
            [1],
            Arr::wrap(1)
        );
    }

    public function testWrapArray(): void
    {
        $this->assertSame(
            [1],
            Arr::wrap([1])
        );
    }

    public function testWrapNull(): void
    {
        $this->assertSame(
            [],
            Arr::wrap(null)
        );
    }
}
