<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait UnshiftTestTrait
{
    public function testUnshift(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(
            4,
            Arr::unshift($array, 0)
        );
        $this->assertArraysAreIdentical(
            [0, 1, 2, 3],
            $array
        );
    }

    public function testUnshiftNArgs(): void
    {
        $array = [3, 4, 5];
        $this->assertSame(
            6,
            Arr::unshift($array, 0, 1, 2)
        );
        $this->assertArraysAreIdentical(
            [0, 1, 2, 3, 4, 5],
            $array
        );
    }
}
