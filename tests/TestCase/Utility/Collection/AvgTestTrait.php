<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait AvgTestTrait
{
    public function testAvg(): void
    {
        $collection = new Collection([2, 4, 3, 4, 1, 5, 3]);

        $this->assertSame(
            3.142857142857143,
            $collection->avg()
        );
    }

    public function testAvgEmpty(): void
    {
        $this->assertNull(
            Collection::empty()->avg()
        );
    }

    public function testAvgPath(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 2,
            ],
            [
                'id' => 2,
                'value' => 4,
            ],
            [
                'id' => 3,
                'value' => 3,
            ],
            [
                'id' => 4,
                'value' => 4,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
            [
                'id' => 6,
                'value' => 5,
            ],
            [
                'id' => 7,
                'value' => 3,
            ],
        ]);

        $this->assertSame(
            3.142857142857143,
            $collection->avg('value')
        );
    }

    public function testAvgPathCallback(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 2,
            ],
            [
                'id' => 2,
                'value' => 4,
            ],
            [
                'id' => 3,
                'value' => 3,
            ],
            [
                'id' => 4,
                'value' => 4,
            ],
            [
                'id' => 5,
                'value' => 1,
            ],
            [
                'id' => 6,
                'value' => 5,
            ],
            [
                'id' => 7,
                'value' => 3,
            ],
        ]);

        $this->assertSame(
            3.142857142857143,
            $collection->avg(static fn(array $item, int $key): int => $item['value'])
        );
    }

    public function testAvgPathDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 2,
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'id' => 3,
                    'value' => 3,
                ],
            ],
            [
                'data' => [
                    'id' => 4,
                    'value' => 4,
                ],
            ],
            [
                'data' => [
                    'id' => 5,
                    'value' => 1,
                ],
            ],
            [
                'data' => [
                    'id' => 6,
                    'value' => 5,
                ],
            ],
            [
                'data' => [
                    'id' => 7,
                    'value' => 3,
                ],
            ],
        ]);

        $this->assertSame(
            3.142857142857143,
            $collection->avg('data.value')
        );
    }
}
