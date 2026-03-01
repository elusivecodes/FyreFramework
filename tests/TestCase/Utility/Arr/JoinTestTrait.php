<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait JoinTestTrait
{
    public function testJoin(): void
    {
        $this->assertSame(
            'a,b,c',
            Arr::join(['a', 'b', 'c'])
        );
    }

    public function testJoinWithSeparator(): void
    {
        $this->assertSame(
            'a-b-c',
            Arr::join(['a', 'b', 'c'], '-')
        );
    }
}
