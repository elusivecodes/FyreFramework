<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait RandomValueTestTrait
{
    public function testRandomValue(): void
    {
        $array = [1, 2, 3];
        $this->assertContains(
            Arr::randomValue($array),
            $array
        );
    }

    public function testRandomValueIsRandom(): void
    {
        $array = [1, 2, 3];
        $test = [];
        for ($i = 0; $i < 100; $i++) {
            $test[] = Arr::randomValue($array);
        }
        $test = Arr::unique($test);
        $this->assertCount(
            3,
            $test
        );
    }
}
