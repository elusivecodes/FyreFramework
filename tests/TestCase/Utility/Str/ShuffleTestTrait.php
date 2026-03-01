<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

use function array_unique;
use function count;
use function sort;

trait ShuffleTestTrait
{
    public function testShuffleIsRandom(): void
    {
        $test = Str::random();
        $test1 = Str::chunk($test);
        sort($test1);

        $strings = [];

        for ($i = 0; $i < 1000; $i++) {
            $string = Str::shuffle($test);
            $test2 = Str::chunk($string);
            sort($test2);
            $this->assertSame(
                $test1,
                $test2
            );
            $strings[] = $string;
        }

        $strings = array_unique($strings);

        $this->assertGreaterThan(
            100,
            count($strings)
        );
    }
}
