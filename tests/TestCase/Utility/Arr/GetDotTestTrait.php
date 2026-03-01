<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait GetDotTestTrait
{
    public function testGetDot(): void
    {
        $this->assertSame(
            3,
            Arr::getDot(['a' => 1, 'b' => ['c' => 2, 'd' => 3]], 'b.d')
        );
    }

    public function testGetDotWithDefault(): void
    {
        $this->assertSame(
            3,
            Arr::getDot(['a' => 1, 'b' => ['c' => 2]], 'b.d', 3)
        );
    }
}
