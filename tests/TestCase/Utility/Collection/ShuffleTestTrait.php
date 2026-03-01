<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

use function array_unique;
use function count;

trait ShuffleTestTrait
{
    public function testShuffleIsRandom(): void
    {
        $collection = Collection::range(1, 10);
        $tests = [];

        for ($i = 0; $i < 1000; $i++) {
            $shuffled = $collection->shuffle()->collect();
            $tests[] = $shuffled->join(',');
            $this->assertSame(
                $collection->toArray(),
                $shuffled->sort()->values()->toArray()
            );
        }

        $tests = array_unique($tests);

        $this->assertGreaterThan(
            100,
            count($tests)
        );
    }
}
