<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait PopTestTrait
{
    public function testPop(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(
            3,
            Arr::pop($array)
        );
    }

    public function testPopModifiesArray(): void
    {
        $array = [1, 2, 3];
        Arr::pop($array);
        $this->assertSame(
            [1, 2],
            $array
        );
    }

    public function testPopWithEmptyArray(): void
    {
        $array = [];
        $this->assertNull(
            Arr::pop($array)
        );
    }
}
