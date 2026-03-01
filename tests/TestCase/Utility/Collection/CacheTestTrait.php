<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;
use Generator;

trait CacheTestTrait
{
    public function testCache(): void
    {
        $i = 1;
        $collection1 = new Collection(static function() use (&$i): Generator {
            yield 'a' => $i++;
            yield 'b' => $i++;
        });

        $collection2 = $collection1->cache();

        $this->assertSame(
            ['a' => 1, 'b' => 2],
            $collection1->toArray()
        );

        $this->assertSame(
            ['a' => 3, 'b' => 4],
            $collection1->toArray()
        );

        $this->assertSame(
            ['a' => 5, 'b' => 6],
            $collection2->toArray()
        );

        $this->assertSame(
            ['a' => 5, 'b' => 6],
            $collection2->toArray()
        );
    }
}
