<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait JsonSerializeTestTrait
{
    public function testJsonSerialize(): void
    {
        $collection = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $collection->jsonSerialize()
        );
    }

    public function testJsonSerializeDeep(): void
    {
        $collection = new Collection(['a' => 1, 'b' => new Collection(['c' => 2])]);

        $this->assertSame(
            ['a' => 1, 'b' => ['c' => 2]],
            $collection->jsonSerialize()
        );
    }
}
