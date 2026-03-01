<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait IncludesTestTrait
{
    public function testIncludes(): void
    {
        $this->assertTrue(
            Collection::range(1, 10)
                ->includes(5)
        );
    }

    public function testIncludesFalse(): void
    {
        $this->assertFalse(
            Collection::range(1, 10)
                ->includes(11)
        );
    }
}
