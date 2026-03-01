<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait IndexOfTestTrait
{
    public function testIndexOf(): void
    {
        $this->assertSame(
            2,
            Arr::indexOf(['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'c')
        );
    }

    public function testIndexOfAssoc(): void
    {
        $this->assertSame(
            'c',
            Arr::indexOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 3, 'f' => 3, 'g' => 5], '3')
        );
    }

    public function testIndexOfWithoutMatch(): void
    {
        $this->assertFalse(
            Arr::indexOf(['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'z')
        );
    }

    public function testIndexOfWithStrict(): void
    {
        $this->assertSame(
            2,
            Arr::indexOf([1, 2, '1', 3, '1', '1', 4, 1], '1', true)
        );
    }
}
