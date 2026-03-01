<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait LastIndexOfTestTrait
{
    public function testLastIndexOf(): void
    {
        $this->assertSame(
            5,
            Arr::lastIndexOf(['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'c')
        );
    }

    public function testLastIndexOfAssoc(): void
    {
        $this->assertSame(
            'f',
            Arr::lastIndexOf(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 3, 'f' => 3, 'g' => 5], '3')
        );
    }

    public function testLastIndexOfWithoutMatch(): void
    {
        $this->assertFalse(
            Arr::lastIndexOf(['a', 'b', 'c', 'd', 'c', 'c', 'e'], 'z')
        );
    }

    public function testLastIndexOfWithStrict(): void
    {
        $this->assertSame(
            5,
            Arr::lastIndexOf([1, 2, '1', 3, '1', '1', 4, 1], '1', true)
        );
    }
}
