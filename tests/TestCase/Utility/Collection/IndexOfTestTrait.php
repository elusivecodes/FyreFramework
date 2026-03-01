<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait IndexOfTestTrait
{
    public function testIndexOf(): void
    {
        $collection = new Collection([1, 2, 3, 3, 4, 5]);

        $this->assertSame(
            2,
            $collection->indexOf(3)
        );
    }

    public function testIndexOfInvalid(): void
    {
        $this->assertNull(
            Collection::range(1, 10)
                ->indexOf(11)
        );
    }
}
