<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait EachTestTrait
{
    public function testEach(): void
    {
        $result = [];
        Collection::range(1, 10)->each(static function(int $value, int $key) use (&$result): void {
            $result[$key] = $value;
        });

        $this->assertSame(
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
            $result
        );
    }
}
