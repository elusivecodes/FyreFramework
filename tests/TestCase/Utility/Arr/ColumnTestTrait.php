<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ColumnTestTrait
{
    public function testColumn(): void
    {
        $this->assertSame(
            [1, 2],
            Arr::column([
                ['a' => 1, 'b' => 2],
                ['a' => 2, 'b' => 3],
            ], 'a')
        );
    }

    public function testColumnMissingValue(): void
    {
        $this->assertSame(
            [1],
            Arr::column([
                ['a' => 1, 'b' => 2],
                ['b' => 3],
            ], 'a')
        );
    }

    public function testColumnNArgs(): void
    {
        $this->assertSame(
            [1, 2, 3],
            Arr::column([
                ['a' => 1, 'b' => 2],
                ['a' => 2, 'b' => 3],
                ['a' => 3, 'b' => 4],
            ], 'a')
        );
    }
}
