<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait PluckDotTestTrait
{
    public function testPluckDot(): void
    {
        $this->assertSame(
            [1, 3],
            Arr::pluckDot([['b' => ['d' => 1]], ['b' => ['d' => 3]]], 'b.d')
        );
    }

    public function testPluckDotMissing(): void
    {
        $this->assertSame(
            [1, null],
            Arr::pluckDot([['b' => ['d' => 1]], ['b' => 0]], 'b.d')
        );
    }
}
