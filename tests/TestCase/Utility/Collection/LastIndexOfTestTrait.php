<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait LastIndexOfTestTrait
{
    public function testLastIndexOf(): void
    {
        $collection = new Collection([1, 2, 3, 3, 4, 5]);

        $this->assertSame(
            3,
            $collection->lastIndexOf(3)
        );
    }

    public function testLastIndexOfInvalid(): void
    {
        $this->assertNull(
            Collection::range(1, 10)
                ->lastIndexOf(11)
        );
    }
}
