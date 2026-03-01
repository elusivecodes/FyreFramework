<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ExceptTestTrait
{
    public function testExcept(): void
    {
        $this->assertSame(
            ['b' => 2, 'd' => 4],
            Arr::except(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], ['a', 'c'])
        );
    }
}
