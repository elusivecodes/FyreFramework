<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait DotTestTrait
{
    public function testDot(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 'a',
            ],
            [
                'id' => 2,
                'value' => 'b',
            ],
        ]);

        $this->assertSame(
            [
                '0.id' => 1,
                '0.value' => 'a',
                '1.id' => 2,
                '1.value' => 'b',
            ],
            $collection->dot()->toArray()
        );
    }

    public function testDotDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 'a',
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 'b',
                ],
            ],
        ]);

        $this->assertSame(
            [
                '0.data.id' => 1,
                '0.data.value' => 'a',
                '1.data.id' => 2,
                '1.data.value' => 'b',
            ],
            $collection->dot()->toArray()
        );
    }
}
