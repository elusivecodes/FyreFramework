<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait IncludesTestTrait
{
    public function testIncludesWithMatch(): void
    {
        $this->assertTrue(
            Arr::includes([1, 2, 3], 2)
        );
    }

    public function testIncludesWithoutMatch(): void
    {
        $this->assertFalse(
            Arr::includes([1, 2, 3], 4)
        );
    }
}
