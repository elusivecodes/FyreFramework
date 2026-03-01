<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait SumOfTestTrait
{
    public function testSumOf(): void
    {
        $this->assertSame(
            55,
            Collection::range(1, 10)->sumOf()
        );
    }

    public function testSumOfEmpty(): void
    {
        $this->assertSame(
            0,
            Collection::empty()->sumOf()
        );
    }

    public function testSumOfPath(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 1,
            ],
            [
                'id' => 2,
                'value' => 2,
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
                'value' => 5,
            ],
            [
                'id' => 6,
                'value' => 6,
            ],
            [
                'id' => 7,
                'value' => 7,
            ],
            [
                'id' => 8,
                'value' => 8,
            ],
            [
                'id' => 9,
                'value' => 9,
            ],
            [
                'id' => 10,
                'value' => 10,
            ],
        ]);

        $this->assertSame(
            55,
            $collection->sumOf('value')
        );
    }

    public function testSumOfPathCallback(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'value' => 1,
            ],
            [
                'id' => 2,
                'value' => 2,
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
                'value' => 5,
            ],
            [
                'id' => 6,
                'value' => 6,
            ],
            [
                'id' => 7,
                'value' => 7,
            ],
            [
                'id' => 8,
                'value' => 8,
            ],
            [
                'id' => 9,
                'value' => 9,
            ],
            [
                'id' => 10,
                'value' => 10,
            ],
        ]);

        $this->assertSame(
            55,
            $collection->sumOf(static fn(array $item, int $key): int => $item['value'])
        );
    }

    public function testSumOfPathDeep(): void
    {
        $collection = new Collection([
            [
                'data' => [
                    'id' => 1,
                    'value' => 1,
                ],
            ],
            [
                'data' => [
                    'id' => 2,
                    'value' => 2,
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
                    'value' => 5,
                ],
            ],
            [
                'data' => [
                    'id' => 6,
                    'value' => 6,
                ],
            ],
            [
                'data' => [
                    'id' => 7,
                    'value' => 7,
                ],
            ],
            [
                'data' => [
                    'id' => 8,
                    'value' => 8,
                ],
            ],
            [
                'data' => [
                    'id' => 9,
                    'value' => 9,
                ],
            ],
            [
                'data' => [
                    'id' => 10,
                    'value' => 10,
                ],
            ],
        ]);

        $this->assertSame(
            55,
            $collection->sumOf('data.value')
        );
    }
}
