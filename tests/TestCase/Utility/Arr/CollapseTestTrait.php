<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait CollapseTestTrait
{
    public function testCollapse(): void
    {
        $this->assertSame(
            [3, 4],
            Arr::collapse([1, 2], [3, 4])
        );
    }

    public function testCollapseAssoc(): void
    {
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4],
            Arr::collapse(['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4])
        );
    }

    public function testCollapseDeep(): void
    {
        $this->assertSame(
            ['a' => ['b' => 2, 'c' => 4, 'd' => 5]],
            Arr::collapse(['a' => ['b' => 2, 'c' => 3]], ['a' => ['c' => 4, 'd' => 5]])
        );
    }

    public function testCollapseNArgs(): void
    {
        $this->assertSame(
            ['a' => 1, 'b' => 2, 'c' => 3],
            Arr::collapse(['a' => 1], ['b' => 2], ['c' => 3])
        );
    }
}
