<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ShiftTestTrait
{
    public function testShift(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(
            1,
            Arr::shift($array)
        );
    }

    public function testShiftModifiesArray(): void
    {
        $array = [1, 2, 3];
        Arr::shift($array);
        $this->assertSame(
            [2, 3],
            $array
        );
    }

    public function testShiftWithEmptyArray(): void
    {
        $array = [];
        $this->assertNull(
            Arr::shift($array)
        );
    }
}
