<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

use function array_unique;

trait RandomValueTestTrait
{
    public function testRandomValue(): void
    {
        $collection = Collection::range(1, 3);
        $value = $collection->randomValue();

        $this->assertTrue(
            $collection->includes($value)
        );
    }

    public function testRandomValueIsRandom(): void
    {
        $collection = Collection::range(1, 3);

        $test = [];
        for ($i = 0; $i < 100; $i++) {
            $test[] = $collection->randomValue();
        }
        $test = array_unique($test);
        $this->assertCount(
            3,
            $test
        );
    }
}
