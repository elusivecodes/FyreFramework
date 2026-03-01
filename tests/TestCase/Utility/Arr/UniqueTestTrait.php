<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait UniqueTestTrait
{
    public function testUnique(): void
    {
        $this->assertSame(
            [0 => 1, 1 => 2, 3 => 3, 5 => 4],
            Arr::unique([1, 2, 1, 3, '01', 4])
        );
    }

    public function testUniqueWithFlags(): void
    {
        $this->assertSame(
            [0 => 1, 1 => 2, 3 => 3, 4 => '01', 5 => 4],
            Arr::unique([1, 2, 1, 3, '01', 4], Arr::SORT_STRING)
        );
    }
}
