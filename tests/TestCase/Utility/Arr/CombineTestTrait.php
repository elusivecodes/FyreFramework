<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait CombineTestTrait
{
    public function testCombine(): void
    {
        $this->assertSame(
            ['a' => 1, 'b' => 2],
            Arr::combine(['a', 'b'], [1, 2])
        );
    }
}
