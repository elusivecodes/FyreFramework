<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ValuesTestTrait
{
    public function testValues(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Arr::values(['a' => 1, 'b' => 2, 'c' => 3])
        );
    }
}
