<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait DivideTestTrait
{
    public function testDivide(): void
    {
        $this->assertSame(
            [
                ['a', 'b', 'c'],
                [1, 2, 3],
            ],
            Arr::divide(['a' => 1, 'b' => 2, 'c' => 3])
        );
    }
}
