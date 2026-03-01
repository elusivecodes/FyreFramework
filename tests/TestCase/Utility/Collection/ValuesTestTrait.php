<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ValuesTestTrait
{
    public function testValues(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertSame(
            [1, 2, 3],
            $collection->values()->toArray()
        );
    }
}
