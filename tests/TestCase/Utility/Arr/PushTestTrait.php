<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait PushTestTrait
{
    public function testPush(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(
            4,
            Arr::push($array, 4)
        );
        $this->assertSame(
            [1, 2, 3, 4],
            $array
        );
    }

    public function testPushNArgs(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(
            6,
            Arr::push($array, 4, 5, 6)
        );
        $this->assertSame(
            [1, 2, 3, 4, 5, 6],
            $array
        );
    }
}
