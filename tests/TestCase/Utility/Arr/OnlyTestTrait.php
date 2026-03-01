<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait OnlyTestTrait
{
    public function testOnly(): void
    {
        $this->assertSame(
            ['a' => 1, 'c' => 3],
            Arr::only(['a' => 1, 'b' => 2, 'c' => 3], ['a', 'c'])
        );
    }
}
