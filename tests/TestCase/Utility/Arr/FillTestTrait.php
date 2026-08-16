<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait FillTestTrait
{
    public function testFill(): void
    {
        $this->assertArraysAreIdentical(
            ['a', 'a', 'a'],
            Arr::fill(3, 'a')
        );
    }
}
